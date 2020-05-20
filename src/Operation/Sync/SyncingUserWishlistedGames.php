<?php

namespace App\Operation\Sync;

use App\Entity\Game;
use App\Entity\User;
use App\Entity\WishlistedGame;
use App\Operation\BaseOperation;
use App\Services\SteamAppIdChecker;
use App\Tools\SteamApi\ApiClient;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Serializer\SerializerInterface;

class SyncingUserWishlistedGames extends BaseOperation
{
    /** @var ApiClient */
    private $apiClient;

    /** @var User */
    private $user;

    /** @var SteamAppIdChecker */
    private $appIdChecker;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ApiClient $apiClient,
        SteamAppIdChecker $appIdChecker
    )
    {
        parent::__construct($entityManager, $serializer);
        $this->apiClient = $apiClient;
        $this->appIdChecker = $appIdChecker;
    }

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws Exception
     */
    public function execute()
    {
        $counters = [
            'new_games' => 0,
            'created' => 0,
            'removed' => 0
        ];

        if (!$this->user)
            throw new Exception(self::class.': UserSteamId is not set');

        $userSteamId = $this->user->getSteamId64();

        $wishlistedIds = $this->apiClient->getUserWishlist($userSteamId);

        $counters['new_games'] = $this->appIdChecker->secureAppIds($wishlistedIds);

        $existedWishlistedGameIds = array_map(
            'reset',
            $this->em->createQueryBuilder()
                ->from(WishlistedGame::class, 'wg')
                ->andWhere('wg.user = :user')
                ->setParameter('user', $this->user)
                ->select('identity(wg.game)')
                ->getQuery()
                ->getArrayResult()
        );

        $idsToRemove = array_diff($existedWishlistedGameIds, $wishlistedIds);

        if (count($idsToRemove))
        {
            $wlGamesToRemove = $this->em->createQueryBuilder()
                ->from(WishlistedGame::class, 'wg')
                ->select('wg')
                ->andWhere('wg.user = :user')
                ->andWhere('identity(wg.game) in (:gameIds)')
                ->setParameters([
                    'user' => $this->user,
                    'gameIds' => $idsToRemove
                ])
                ->getQuery()
                ->getResult();

            foreach ($wlGamesToRemove as $wlGame) {
                $this->em->remove($wlGame);
                $counters['removed']++;
            }

            $this->em->flush();
        }

        $gameIdsToAdd = array_diff($wishlistedIds, $existedWishlistedGameIds);

        foreach ($gameIdsToAdd as $gameId) {

            /** @var Game $game */
            $game = $this->em->getReference(Game::class, $gameId);

            $wishlistedGame = new WishlistedGame();
            $wishlistedGame->setUser($this->user)
                ->setGame($game);

            $this->em->persist($wishlistedGame);
            $counters['created']++;
        }

        $this->em->flush();

        $this->em->clear(WishlistedGame::class);
        $this->em->clear(Game::class);

        $counters['total'] = count($wishlistedIds);

        return $counters;
    }
}
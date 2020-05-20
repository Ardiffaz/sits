<?php

namespace App\Operation\Sync;

use App\Entity\Game;
use App\Entity\OwnedGame;
use App\Entity\User;
use App\Operation\BaseOperation;
use App\Services\SteamAppIdChecker;
use App\Tools\SteamApi\ApiClient;
use App\Tools\SteamApi\SteamResponseException;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Serializer\SerializerInterface;

class SyncingUserOwnedGames extends BaseOperation
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

        $isSkipped = false;

        if (!$this->user)
            throw new Exception(self::class.': UserSteamId is not set');

        $userSteamId = $this->user->getSteamId64();

        $ownedIdsV1 = [];
        $ownedIdsV2 = [];

        try {
            $ownedIdsV2 = $this->apiClient->getOwnedGamesApi($userSteamId);
            $ownedIdsV1 = $this->apiClient->getOwnedGames($this->user->getProfileUrl());
        }
        catch (SteamResponseException $e)
        {
            // something went wrong on Steam side (but it's not 'too many requests' wrong)
            // so we should just skip the user
            $isSkipped = true;
            $this->user->setIsSkipped(true);
            $this->em->flush();
        }

        $countV1 = is_array($ownedIdsV1) ? count($ownedIdsV1) : 0;
        $countV2 = is_array($ownedIdsV2) ? count($ownedIdsV2) : 0;

        // if one of them is null and another is not, then sth went wrong.
        if ( ( ($countV1 == 0) && ($countV2 != 0) ) || ( ($countV2 == 0) && ($countV1 != 0) ) )
        {
            $isSkipped = true;
            $this->user->setIsSkipped(true);
            $this->em->flush();
        }

        if (!$isSkipped && ($this->user->getisSkipped() === true) )
        {
            $this->user->setIsSkipped(false);
            $this->em->flush();
        }

        if ($isSkipped)
            $counters['is_skipped'] = true;

        if (!$ownedIdsV1)
            $ownedIdsV1 = [];

        if (!$ownedIdsV2)
            $ownedIdsV2 = [];

        $ownedIds = array_unique(array_merge($ownedIdsV2, $ownedIdsV1));
        $counters['new_games'] = $this->appIdChecker->secureAppIds($ownedIds);

        /** @var OwnedGame[] $existedOwnedGameIds */
        $existedOwnedGameIds = array_map(
            'reset',
            $this->em->createQueryBuilder()
                ->from(OwnedGame::class, 'og')
                ->andWhere('og.user = :user')
                ->setParameter('user', $this->user)
                ->select('identity(og.game)')
                ->getQuery()
                ->getArrayResult()
        );

        /** @var OwnedGame[] $notActiveExistedApps */
        $notActiveExistedApps = $this->em->createQueryBuilder()
            ->from(OwnedGame::class, 'og')
            ->select('og')
            ->andWhere('og.user = :user')
            ->setParameter('user', $this->user)
            ->andWhere('og.active = false')
            ->getQuery()
            ->getResult();

        foreach ($notActiveExistedApps as $ownedGame) {
            $ownedGame->setActive(true);
        }


        $idsToRemove = $ownedIds ? array_diff($existedOwnedGameIds, $ownedIds) : [];

        if (count($idsToRemove) && !$isSkipped)
        {
            /** @var OwnedGame[] $ownedGamesToRemove */
            $ownedGamesToRemove = $this->em->createQueryBuilder()
                ->from(OwnedGame::class, 'og')
                ->select('og')
                ->andWhere('og.user = :user')
                ->andWhere('identity(og.game) in (:gameIds)')
                ->setParameters([
                    'user' => $this->user,
                    'gameIds' => $idsToRemove
                ])
                ->getQuery()
                ->getResult();

            foreach ($ownedGamesToRemove as $ownedGame) {
                //$this->em->remove($ownedGame);
                $ownedGame->setActive(false);
                $counters['removed']++;
            }
        }

        $gameIdsToAdd = $ownedIds ? array_diff($ownedIds, $existedOwnedGameIds) : [];

        foreach ($gameIdsToAdd as $gameId) {

            /** @var Game $game */
            $game = $this->em->getReference(Game::class, $gameId);

            $ownedGame = new OwnedGame();
            $ownedGame->setGame($game)
                ->setUser($this->user)
                ->setActive(true);

            $this->em->persist($ownedGame);

            $counters['created']++;
        }

        $this->em->flush();

        $now = new DateTime();
        $this->user->setLastUpdated($now);
        $this->em->flush();

        $this->em->clear(OwnedGame::class);
        $this->em->clear(Game::class);

        $counters['total'] = count($existedOwnedGameIds) + count($gameIdsToAdd);

        return $counters;
    }
}
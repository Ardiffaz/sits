<?php

namespace App\Services;

use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;

class SteamAppIdChecker
{
    /** @var EntityManagerInterface */
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @param array $appIds
     * @return int
     */
    public function secureAppIds($appIds)
    {
        if (!$appIds || !count($appIds))
            return 0;

        $createdCount = 0;

        $gameIds = array_map(
            'reset',
            $this->em->createQueryBuilder()
                ->from(Game::class, 'g')
                ->andWhere('g.id in (:appIds)')
                ->setParameter('appIds', $appIds)
                ->select('g.id')
                ->getQuery()
                ->getArrayResult()
        );

        $notExistedGameIds = array_diff($appIds, $gameIds);

        foreach ($notExistedGameIds as $newGameId) {
            $game = new Game();
            $game->setId($newGameId);
            $this->em->persist($game);

            $gameIds[] = $newGameId;
            $createdCount++;
        }

        $this->em->flush();

        return $createdCount;
    }
}
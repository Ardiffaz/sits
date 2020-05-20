<?php

namespace App\Operation\Sync;

use App\Entity\Game;
use App\Operation\BaseOperation;
use App\Tools\SteamApi\ApiClient;
use App\Tools\SteamApi\Object\AppBasic;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Serializer\SerializerInterface;

class SyncingSteamGames extends BaseOperation
{
    /** @var ApiClient */
    protected $apiClient;

    protected const CHUNK_SIZE = 2000;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer, ApiClient $apiClient)
    {
        parent::__construct($entityManager, $serializer);
        $this->apiClient = $apiClient;
    }

    /**
     * @param bool $v2
     * @return AppBasic[]|array|false
     * @throws Exception
     */
    public function getApps($v2 = false)
    {
        /** @var AppBasic[] $apps */
        $apps = $v2 ? $this->apiClient->getAppList() : $this->apiClient->getAppListV2();

        if ($apps === null)
            return [];

        $appIds = array_map(
            function ($app) {
                return $app->appId;
            },
            $apps
        );

        $apps = array_combine($appIds, $apps);

        return $apps;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function execute()
    {
        $counters = [
            'updated' => 0,
            'created' => 0
        ];

        $existedAppIds = array_map(
            'reset',
            $this->em->createQueryBuilder()
                ->from(Game::class, 'g')
                ->select('g.id')
                ->getQuery()
                ->getScalarResult()
        );

        $apps = $this->getApps(false);
        $appsV2 = $this->getApps(true);

        $apps = $appsV2 + $apps;

        $newAppIds = array_diff( array_keys($apps), $existedAppIds );

        $existedAppIdsChunks = array_chunk(
            array_intersect(
                array_keys($apps),
                $existedAppIds
            ),
            self::CHUNK_SIZE
        );

        foreach ($existedAppIdsChunks as $existChunk) {

            /** @var Game[] $existedGames */
            $existedGames = $this->em->getRepository(Game::class)->findBy(['id' => $existChunk]);

            foreach ($existedGames as $existedGame) {
                $gameId = $existedGame->getId();

                $newName = $apps[ $gameId ]->name;

                if ($existedGame->getName() !== $newName)
                {
                    $existedGame->setName($newName);
                    $counters['updated']++;
                }
            }
            $this->em->flush();
            $this->em->clear();
        }

        $newAppIdsChunks = array_chunk($newAppIds, self::CHUNK_SIZE);

        foreach ($newAppIdsChunks as $newChunk)
        {
            foreach ($newChunk as $newId) {
                $app = $apps[$newId];

                $game = new Game();
                $game->setId( $app->appId )
                    ->setName( $app->name );

                $this->em->persist($game);
                $counters['created']++;
            }

            $this->em->flush();
            $this->em->clear();
        }

        $this->em->flush();
        $this->em->clear();

        return $counters;
    }
}
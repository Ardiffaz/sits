<?php

namespace App\Operation\Sync;

use App\Entity\Game;
use App\Entity\Gift;
use App\Enum\AppType;
use App\Operation\BaseOperation;
use App\Tools\SteamApi\ApiClient as SteamApiClient;
use App\Tools\SteamApi\Object\AppDetails as SteamAppDetails;
use App\Tools\SteamApi\Object\AppReviews;
use App\Tools\SteamSpy\ApiClient as SteamSpyApiClient;
use App\Tools\SteamSpy\Object\AppDetails as SteamSpyAppDetails;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Serializer\SerializerInterface;

class SyncingAppDetails extends BaseOperation
{
    private const TRADING_CARDS_TEXT = 'Steam Trading Cards';

    private const UPDATE_TYPE_ALL = 'all';
    private const UPDATE_TYPE_GIFTS = 'gifts';

    /** @var SteamSpyApiClient */
    private $steamSpyApiClient;

    /** @var SteamApiClient */
    private $steamApiClient;

    /** @var array */
    private $steamIds = [];

    /** @var string string */
    private $updateType;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        SteamSpyApiClient $steamSpyApiClient,
        SteamApiClient $steamApiClient
        )
    {
        parent::__construct($entityManager, $serializer);
        $this->steamSpyApiClient = $steamSpyApiClient;
        $this->steamApiClient = $steamApiClient;
    }

    public function setSteamId(int $steamId)
    {
        $this->steamIds = [$steamId];
        return $this;
    }

    public function setSteamIds(array $steamIds)
    {
        $this->steamIds = $steamIds;
        return $this;
    }

    public function setUpdateType(string $updateType)
    {
        $allowedTypes = [self::UPDATE_TYPE_ALL, self::UPDATE_TYPE_GIFTS];

        $this->updateType = (in_array($updateType, $allowedTypes)) ? $updateType : self::UPDATE_TYPE_ALL;

        return $this;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function execute()
    {
        if ($this->updateType == self::UPDATE_TYPE_GIFTS)
        {
            $games = $this->em->createQueryBuilder()
                ->from(Gift::class, 'gift')
                ->leftJoin('gift.game', 'game')
                ->andWhere('gift.giveaway is null')
                ->orderBy('game.id')
                ->groupBy('game.id')
                ->select('game.id')
                ->getQuery()
                ->getResult();

            $gameIds = array_map('reset', $games);

            $this->setSteamIds($gameIds);

        }
        elseif ($this->updateType == self::UPDATE_TYPE_ALL)
        {
            $this->steamIds = [];
        }

        if (empty($this->steamIds))
        {

            $games = $this->em->createQueryBuilder()
                ->from(Game::class,'g')
                ->select('g')
                ->orderBy('g.lastUpdated', 'ASC')
                ->setMaxResults(1000)
                ->getQuery()
                ->getResult();

            $gameIds = array_map('reset', $games);

            $this->setSteamIds($gameIds);
        }

        $lastUpdatedGame = null;
        $updatedCount = 0;
        $cycleCount = 0;

        foreach ($this->steamIds as $steamGameId) {

            $cycleCount++;

            if ($cycleCount > 198)
            {
                sleep(200);
                $cycleCount = 0;
            }

            /** @var SteamSpyAppDetails $appDetails */
            $appDetails = $this->steamSpyApiClient->getAppDetails($steamGameId);

            $game = $this->em->find(Game::class, $steamGameId);

            if (!$game)
            {
                if ($appDetails->name != null && $appDetails->name != '')
                    throw new Exception('This is probably a wrong app ID, game name not found on SteamSpy.');

                $game = new Game();
                $game->setId( $appDetails->id );
            }

            $priceUsd = $appDetails->initialprice / 100;

            $game->setPriceUsd($priceUsd);

            if (($game->getName() === null) || ($game->getName() === ''))
                $game->setName( $appDetails->name );

            try {
                /** @var SteamAppDetails $steamAppDetails */
                $steamAppDetails = $this->steamApiClient->getAppDetails($steamGameId);
            } catch (Exception $e) {
                $game->setType( AppType::UNDEFINED );
            }

            if ($steamAppDetails)
            {
                $hasTradingCards = false;
                if ($steamAppDetails->categories !== null)
                {
                    foreach ($steamAppDetails->categories as $category) {
                        if ($category['description'] == self::TRADING_CARDS_TEXT)
                            $hasTradingCards = true;
                    }
                }

                $game
                    ->setType( $steamAppDetails->type )
                    ->setAchievementCount( $steamAppDetails->achievements )
                    ->setPriceRub( $steamAppDetails->priceRub )
                    ->setHasTradingCards($hasTradingCards);
            }

            try {
                $steamAppReviews = $this->steamApiClient->getAppReviews($steamGameId);
            } catch (Exception $e) {

                if (($game->getRating() > 0) || ($game->getReviewCount() > 0))
                    $steamAppReviews = false;
                else
                    $steamAppReviews = (new AppReviews());
            }

            if ($steamAppReviews)
            {
                $game
                    ->setRating( $steamAppReviews->rating )
                    ->setReviewCount( $steamAppReviews->reviewCount );
            }

            /** @noinspection PhpUnhandledExceptionInspection */
            $game->setLastUpdated( new DateTime() );

            $lastUpdatedGame = $game;
            $updatedCount++;

            $this->em->persist($game);
            $this->em->flush();
        }

        return ['game' => $lastUpdatedGame, 'count' => $updatedCount];
    }
}
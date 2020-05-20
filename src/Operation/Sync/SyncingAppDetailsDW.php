<?php

namespace App\Operation\Sync;

use App\Entity\Game;
use App\Operation\BaseOperation;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Goutte\Client as GoutteClient;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class SyncingAppDetailsDW extends BaseOperation
{
    /** @var GoutteClient  */
    private $goutteClient;

    private $dataWorldApiKey;

    public function __construct(string $dataWorldApiKey, GoutteClient $goutteClient, EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        parent::__construct($entityManager, $serializer);
        $this->dataWorldApiKey = $dataWorldApiKey;
        $this->goutteClient = $goutteClient;
    }

    public function execute()
    {
        $headers = [
            'Authorization' => 'Bearer '.$this->dataWorldApiKey,
            'Accept' => 'application/json'
        ];

        $this->goutteClient->resetHeaders();
        foreach ($headers as $headerName => $headerValue) {
            $this->goutteClient->setHeader($headerName, $headerValue);
        }

        // urlencode('query=SELECT * FROM products LIMIT 30')

        $crawler = $this->goutteClient->request(
            Request::METHOD_POST,
            'https://api.data.world/v0/sql/insideone/steam-search',
            [
                'query' => "SELECT product.sid, product.title, product.votes, product.rating, usd_price.value as usd_price_value, rub_price.value as rub_price_value
FROM products product
LEFT JOIN prices usd_price ON usd_price.psid=product.sid AND usd_price.currency='USD' AND usd_price.type=1
LEFT JOIN prices rub_price ON rub_price.psid=product.sid AND rub_price.currency='RUB' AND rub_price.type=1
WHERE product.sid LIKE 'A%'"
            ]
        );

        /** @var Response $result */
        $result = $this->goutteClient->getResponse();
        $apps = json_decode($result->getContent(), true);

        $count = 0;
        $countToFlush = 0;

        foreach ($apps as $app) {
            $appId = str_replace('A', '', $app['sid']);
            $isNew = false;
            $game = $this->em->find(Game::class, $appId);

            if (!$game)
            {
                $game = new Game();
                $game->setId($appId);
                $isNew = true;
            }

            $game->setName($app['title'])
                ->setReviewCount($app['votes'])
                ->setRating($app['rating'])
                ->setPriceUsd($app['usd_price_value'])
                ->setPriceRub($app['rub_price_value']);

            /** @noinspection PhpUnhandledExceptionInspection */
            $game->setLastUpdated( new DateTime() );

            if ($isNew)
                $this->em->persist($game);

            $count++;
            $countToFlush++;

            if ($countToFlush >= 200)
            {
                $this->em->flush();
                $countToFlush = 0;
            }
        }
        $this->em->flush();

        return ['count' => $count];
    }

}
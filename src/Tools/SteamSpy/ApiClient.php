<?php

namespace App\Tools\SteamSpy;

use App\Tools\SteamSpy\Object\AppDetails;
use Goutte\Client as GoutteClient;
use Symfony\Component\Serializer\SerializerInterface;

class ApiClient
{
    const BASE_API_URL = 'https://steamspy.com/api.php';

    /** @var GoutteClient  */
    private $goutteClient;

    /** @var SerializerInterface */
    protected $serializer;

    public function __construct(GoutteClient $goutteClient, SerializerInterface $serializer)
    {
        $this->goutteClient = $goutteClient;
        $this->serializer = $serializer;
    }

    protected function getJsonResponse(string $url)
    {
        $this->goutteClient->request('GET', $url);
        $response = $this->goutteClient->getResponse()->getContent();

        return $response;
    }

    public function getAppDetails($steamGameId)
    {
        $method = 'appdetails';

        $url = $this::BASE_API_URL.'?'.http_build_query(['request' => $method, 'appid' => $steamGameId]);

        $response = $this->getJsonResponse($url);

        /** @var AppDetails $appDetails */
        $appDetails = $this->serializer->deserialize(
            $response,
            AppDetails::class,
            'json'
        );

        return $appDetails;
    }
}
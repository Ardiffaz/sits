<?php

namespace App\Tools\SteamApi;

use DOMElement;
use Exception;
use Goutte\Client as GoutteClient;
use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Serializer\SerializerInterface;

abstract class BaseApiClient
{
    private $key = '';
    const BASE_API_URL = 'https://api.steampowered.com/';
    const BASE_COMMUNITY_URL = 'https://steamcommunity.com/';
    const BASE_PROFILE_URL = self::BASE_COMMUNITY_URL . 'profiles/';
    const BASE_APPDETAILS_URL = 'https://store.steampowered.com/api/appdetails/';
    const BASE_APPREVIEWS_URL = 'https://store.steampowered.com/appreviews/';

    const OWNED_GAMES_NEEDLE = 'var g_rgGameData = ';
    const WISHLISTED_GAMES_NEEDLE = 'var g_rgWishlistData = ';
    const GAMES_NEEDLE = 'var rgGames = ';

    /** @var GoutteClient  */
    private $goutteClient;

    /** @var SerializerInterface */
    protected $serializer;

    public function __construct(string $apiKey, GoutteClient $goutteClient, SerializerInterface $serializer)
    {
        $this->key = $apiKey;
        $this->goutteClient = $goutteClient;
        $this->serializer = $serializer;
    }

    /**
     * @param string $url
     * @param array $headers
     * @return ApiResponse
     * @throws Exception
     */
    protected function handleRequest(string $url, array $headers = [])
    {
        $this->goutteClient->resetHeaders();
        foreach ($headers as $headerName => $headerValue) {
            $this->goutteClient->setHeader($headerName, $headerValue);
        }

        $triesCount = 0;
        $responseStatus = 0;
        $responseContent = '';

        while (($triesCount <= 3) && ($responseStatus != 200))
        {
            if ($triesCount > 0)
                sleep(20);
            else
                sleep(1);

            $crawler = $this->goutteClient->request('GET', $url);

            /** @var Response $response */
            $response = $this->goutteClient->getResponse();
            $responseContent = $response->getContent();
            $responseStatus = $response->getStatus();

            if ($responseStatus == 429)
            {
                throw new Exception(
                    'Steam API: Too many requests. Status '.$responseStatus
                );
            }

            $triesCount++;
        }

        if ($responseStatus != 200)
        {
            throw new SteamResponseException(
                'Steam API: Api call failed due to an external server error. Status '.
                $responseStatus.'. Response: '.$responseContent
            );
        }

        if (!$responseContent)
        {
            throw new SteamResponseException('Steam API: Api response is empty. Status '.$responseStatus);
        }

        return new ApiResponse($crawler, $response);
    }

    /**
     * @param string $urlService
     * @param array $urlParams
     * @return mixed
     * @throws Exception
     */
    protected function getApiJsonResponse(string $urlService, array $urlParams = [])
    {
        $urlParams['key'] = $this->key;
        $url = self::BASE_API_URL.$urlService.'?'.urldecode(http_build_query($urlParams));

        return $this->getJsonResponse($url);
    }

    /**
     * @param string $url
     * @return mixed
     * @throws Exception
     */
    protected function getJsonResponse(string $url)
    {
        $apiResponse = $this->handleRequest($url);
        $responseContent = $apiResponse->getResponse()->getContent();

        $contentType = $apiResponse->getContentType();
        if (strpos($contentType, 'application/json' ) === false)
            throw new Exception('The answer type is not json: "'.$contentType.'" is given');

        $decodedResponse = json_decode($responseContent, false);

        return $decodedResponse;
    }

    /**
     * @param string $url
     * @return Crawler
     * @throws Exception
     */
    protected function getXmlResponse(string $url)
    {
        $apiResponse = $this->handleRequest($url);
        $crawler = $apiResponse->getCrawler();

        $contentType = $apiResponse->getContentType();

        if (strpos($contentType, 'text/xml') === false)
            throw new Exception('The answer type is not XML: "'.$contentType.'" is given');

        if ($crawler->filterXPath('response/error')->count())
            throw new Exception('Error in XML answer: '.$crawler->filterXPath('response/error')->text());


        return $crawler;
    }

    /**
     * @param string $url
     * @return Crawler
     * @throws Exception
     */
    protected function getHtmlResponse(string $url)
    {
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:67.0) Gecko/20100101 Firefox/67.0',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'ru,en-US;q=0.7,en;q=0.3',
            'Accept-Encoding' => 'gzip, deflate, br',
            'DNT' => '1',
            'Connection' => 'keep-alive',
            'Upgrade-Insecure-Requests' => '1',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'no-cache'
        ];

        $apiResponse = $this->handleRequest($url, $headers);

        $crawler = $apiResponse->getCrawler();

        return $crawler;
    }

    protected function getXml(Crawler $xmlContent)
    {
        $xml = '';
        foreach ($xmlContent->getNode(0)->childNodes as $child) {
            /** @var DOMElement $child */
            $xml .= $child->ownerDocument->saveXML($child);
        }

        return $xml;
    }

    /**
     * @param string $needle
     * @param string $html
     * @return mixed|null
     */
    protected function getJsonFromHtml(string $needle, string $html)
    {
        $needlePos = strpos($html, $needle);
        if ($needlePos === false) {
            return null;
        }

        $nextLinePos = strpos($html, "\n", $needlePos);
        $needleLen = strlen($needle);

        $json = trim(substr($html, $needlePos + $needleLen, $nextLinePos - $needlePos - $needleLen), "\r\t\n ;");

        $rawJson = json_decode($json);

        return $rawJson;

    }

    protected function getAppIdsByNeedle(string $needle, string $html)
    {
        $rawData = $this->getJsonFromHtml($needle, $html);
        $appIds = [];

        if ($rawData)
        {
            $appIds = array_map(function ($item){
                return $item->appid;
            }, $rawData);
        }

        return $appIds;
    }
}
<?php

namespace App\Tools\SteamApi;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\BrowserKit\Response;

class ApiResponse
{
    /** @var Crawler */
    private $crawler;

    private $response;

    public function __construct(Crawler $crawler, Response $response)
    {
        $this->crawler = $crawler;
        $this->response = $response;
    }

    /**
     * @return Crawler
     */
    public function getCrawler(): Crawler
    {
        return $this->crawler;
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * @return array|string
     */
    public function getContentType()
    {
        return $this->getResponse()->getHeader('Content-type');
    }

}
<?php

namespace App\Controller;

use App\Tools\SteamApi\ApiClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends BaseController
{

    /** @var ApiClient */
    private $apiClient;

    /** @var EntityManagerInterface */
    private $em;

    public function __construct(ApiClient $apiClient, EntityManagerInterface $entityManager)
    {
        $this->apiClient = $apiClient;
        $this->em = $entityManager;
    }

    /**
     * @Route("/", name="index")
     * @Route("/login", name="login")
     * @Route("/{vue_routing}", name="vue_routing", requirements={"vue_routing"="^(?!api|_(profiler|wdt)).*"})
     * @return Response
     */
    public function index()
    {
        return $this->render('base.html.twig', []);
    }

    /**
     * @Route("/api/test", name="api_test")
     * @param Request $request
     * @return Response
     */
    public function apiTest(
        Request $request
    )
    {
        // for testing purposes only.

        $data = [];

        return $this->render('base.html.twig', ['test' => $data]);
    }
}
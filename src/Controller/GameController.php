<?php

namespace App\Controller;

use App\Operation\Game\FindingGames;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends BaseController
{

    /**
     * @Route("/api/games", name="api_games_find")
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function findGame(Request $request, FindingGames $findingGames)
    {
        $searchParam = $request->get('q');
        $pageNumber = (int)$request->get('page');

        if (!$pageNumber)
            $pageNumber = 1;

        try {
            $gamesResult = $findingGames->setSearchParam($searchParam)->setPageNumber($pageNumber)->execute();
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        return $this->response([
            'games' => $gamesResult['games'],
            'curPageNumber' => $gamesResult['curPageNumber'],
            'maxPageNumber' => $gamesResult['maxPageNumber']
        ]);
    }
}
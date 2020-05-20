<?php

namespace App\Controller;

use App\Operation\Sync\SyncingAppDetails;
use App\Operation\Sync\SyncingAppDetailsDW;
use App\Operation\Sync\SyncingSteamGames;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SyncController extends BaseController
{

    /**
     * @Route("/api/sync/games", name="api_sync_games")
     * @return JsonResponse|Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function syncSteamGames(SyncingSteamGames $syncingSteamGames)
    {
        try {
            $result = $syncingSteamGames->execute();
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage().$e->getTraceAsString());
        }

        return $this->response(['result' => $result]);
    }

    /**
     * @Route("/api/sync/app_details", name="api_sync_app_details")
     * @param Request $request
     * @param SyncingAppDetails $syncingAppDetails
     * @return JsonResponse|Response
     */
    public function syncSteamAppDetails(Request $request, SyncingAppDetails $syncingAppDetails)
    {
        $steamId = $request->get('id');
        $type = $request->get('type');

        if ($steamId)
            $syncingAppDetails->setSteamId($steamId);

        if ($type)
            $syncingAppDetails->setUpdateType($type);

        try {
            $result = $syncingAppDetails->execute();
        } catch (Exception $e) {
            return $this->errorResponse([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $this->response(['game' => $result['game'], 'id' => $steamId, 'count' => $result['count']]);
    }

    /**
     * @Route("/api/sync/app_details_dw", name="api_sync_app_details_dw")
     * @return JsonResponse|Response
     */
    public function syncSteamAppDetailsFromDataWorld(SyncingAppDetailsDW $syncingAppDetailsDW)
    {
        try {
            $result = $syncingAppDetailsDW->execute();
        } catch (Exception $e) {
            return $this->errorResponse([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $this->response(['count' => $result['count']]);
    }
}
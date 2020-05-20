<?php

namespace App\Controller;

use App\Operation\FetchSteam\FetchingGroup;
use App\Operation\FetchSteam\FetchingUserGroups;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FetchSteamController extends BaseController
{
    /**
     * @Route("/api/fetch_steam/group/{groupId}", name="api_fetch_steam_group")
     * @param $groupId
     * @return Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function fetchSteamGroup($groupId, FetchingGroup $fetchingSteamGroup)
    {
        try {
            $group = $fetchingSteamGroup->setGroupId64($groupId)->execute();
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        return $this->response(['group' => $group]);
    }

    /**
     * @Route("/api/fetch_steam/user/{userSteamId}/groups", name="api_fetch_steam_user_groups")
     * @param $userSteamId
     * @return Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function fetchUserGroups($userSteamId, FetchingUserGroups $fetchingSteamUserGroups)
    {
        try {
            $groups = $fetchingSteamUserGroups->setUserSteamId64($userSteamId)->execute();
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        return $this->response(['groups' => $groups]);
    }

}
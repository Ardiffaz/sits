<?php

namespace App\Controller;

use App\Entity\Group;
use App\Operation\Group\UpdatingGroup;
use App\Services\ReferenceExtractor;
use App\Tools\SteamApi\ApiClient;
use App\Operation\Group\CheckingGames;
use App\Operation\Group\RemovingGroup;
use App\Operation\Group\SavingGroupFromSteam;
use App\Operation\Group\GettingGroupList;
use App\Operation\User\GettingUserList;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GroupController extends BaseController
{
    /** @var ApiClient */
    private $apiClient;

    public function __construct(ApiClient $apiClient, ReferenceExtractor $referenceExtractor)
    {
        parent::__construct($referenceExtractor);
        $this->apiClient = $apiClient;
    }

    /**
     * @Route("/api/groups/add", name="api_add_group", methods={"POST"})
     * @param Request $request
     * @param SavingGroupFromSteam $savingGroup
     * @return JsonResponse|Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function addGroup(Request $request, SavingGroupFromSteam $savingGroup)
    {
        $groupSteamId = $this->getRequestParam($request, 'groupSteamId');

        try {
            $group = $savingGroup->setGroupSteamId($groupSteamId)->execute();

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        return $this->response(
            [ 'group' => $group, 'groupSteamId' => $groupSteamId ],
            [ 'groups' => ['export', 'export-members'] ]
        );
    }

    /**
     * @Route("/api/groups/update", name="api_save_group", methods={"POST"})
     * @param Request $request
     * @param SavingGroupFromSteam $savingGroup
     * @return JsonResponse|Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function updateGroup(Request $request, SavingGroupFromSteam $savingGroup)
    {
        $groupId = $this->getRequestParam($request, 'groupId');

        try {
            $group = $savingGroup->setGroupId($groupId)->execute();

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage().$e->getTraceAsString());
        }

        return $this->response(
            [ 'group' => $group, 'groupId' => $groupId ],
            [ 'groups' => ['export', 'export-members'] ]
        );
    }

    /**
     * @Route("/api/groups/{group}/change_sg_link", name="api_update_group_sg_link", methods={"POST"})
     * @param Request $request
     * @param UpdatingGroup $updatingGroup
     * @return JsonResponse|Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function changeGroupSgLink(Request $request, Group $group, UpdatingGroup $updatingGroup)
    {
        $sgLink = $this->getRequestParam($request, 'sgLink');

        try {
            $group = $updatingGroup->setGroup($group)->setSgLink($sgLink)->execute();

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage().$e->getTraceAsString());
        }

        return $this->response(
            [ 'group' => $group ],
            [ 'groups' => ['export', 'export-members'] ]
        );
    }

    /**
     * @Route("/api/groups/remove", name="api_remove_group", methods={"POST"})
     * @param Request $request
     * @param RemovingGroup $removingGroup
     * @return JsonResponse|Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function removeGroup(Request $request, RemovingGroup $removingGroup)
    {
        $groupId = $this->getRequestParam($request, 'groupId');

        try {
            $result = $removingGroup->setGroupId($groupId)->execute();

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        return $this->response(['removed' => $result]);
    }

    /**
     * @Route("/api/groups", name="api_groups")
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function getList(Request $request, GettingGroupList $gettingGroupList)
    {
        $withMembers = $request->get('with_members') ? true : false;

        $groups = $gettingGroupList->execute();

        $exportGroups = ($withMembers) ? ['export', 'export-members'] : ['export'];

        return $this->response([ 'groups' => $groups ], [ 'groups' =>  $exportGroups]);

    }
    /**
     * @Route("/api/groups/{group}", name="api_group_details")
     * @param Request $request
     * @param GettingUserList $gettingUserList
     * @return JsonResponse|Response
     */
    public function getGroupDetails(Request $request, Group $group, GettingUserList $gettingUserList)
    {

        try {
            $members = $gettingUserList->setGroup($group)->execute();

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
        return $this->response(
            ['group' => $group, 'members' => $members],
            [
                'groups' => ['export', 'export-count']
            ]
        );
    }

    /**
     * @Route("/api/groups/{group}/games", name="api_group_game_checker")
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function checkGames(Request $request, Group $group, GettingUserList $gettingUserList, CheckingGames $checkingGames)
    {
        $searchParam = $request->get('q');
        $pageNumber = $request->get('page');

        try {
            $members = $gettingUserList->setGroup($group)->execute();
            $gamesResult =
                $checkingGames
                    ->setGroup($group)
                    ->setSearchParam($searchParam)
                    ->setPageNumber($pageNumber)
                    ->execute();
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        return $this->response(
            [
                'group' => $group,
                'members' => $members,
                'games' => $gamesResult['games'],
                'curPageNumber' => $gamesResult['curPageNumber'],
                'maxPageNumber' => $gamesResult['maxPageNumber']
            ],
            [
                'groups' => ['export', 'export-game-count', 'export-count']
            ]
        );

    }

}
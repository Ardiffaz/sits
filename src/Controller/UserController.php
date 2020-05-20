<?php

namespace App\Controller;

use App\Entity\User;
use App\Operation\User\GrantingUserRole;
use App\Operation\User\RevokingUserRole;
use App\Services\ReferenceExtractor;
use App\Tools\SteamApi\ApiClient;
use App\Operation\Sync\SyncingUserGames;
use App\Operation\User\GettingUserList;
use App\Operation\User\RemovingUser;
use App\Operation\User\UpdatingUser;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends BaseController
{
    private $apiClient;

    public function __construct(ApiClient $apiClient, ReferenceExtractor $referenceExtractor)
    {
        parent::__construct($referenceExtractor);
        $this->apiClient = $apiClient;
    }

    /**
     * @Route("/api/users", name="api_users")
     * @param GettingUserList $gettingUserList
     * @return JsonResponse|Response
     */
    public function getList(GettingUserList $gettingUserList)
    {
        try {
            $users = $gettingUserList->execute();
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        return $this->response([ 'users' => $users ], ['groups' => ['export', 'export-count', 'export-groups']]);
    }

    /**
     * @Route("/api/users/gifters", name="api_users_gifters")
     * @param GettingUserList $gettingUserList
     * @return JsonResponse|Response
     */
    public function getGifterList(GettingUserList $gettingUserList)
    {
        try {
            $users = $gettingUserList->setTypeGifters()->execute();
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        return $this->response([ 'users' => $users ], ['groups' => ['shortlist']]);
    }

    /**
     * @Route("/api/users/with_roles", name="api_users_with_roles")
     * @param GettingUserList $gettingUserList
     * @return JsonResponse|Response
     */
    public function getUsersWithRolesList(GettingUserList $gettingUserList)
    {
        try {
            $users = $gettingUserList->setTypeWithRoles()->execute();
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        return $this->response([ 'users' => $users ], ['groups' => ['shortlist', 'export-roles']]);
    }

    /**
     * @Route("/api/users/update", name="api_users_update_games", methods={"POST"})
     * @param Request $request
     * @param SyncingUserGames $syncingUserGames
     * @return JsonResponse|Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function updateUserGames(Request $request, SyncingUserGames $syncingUserGames)
    {
        $userId = $this->getRequestParam($request, 'userId');

        try {
            $counters = $syncingUserGames->setUserId($userId)->execute();
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        return $this->response([ 'counters' => $counters, 'userId' => $userId ]);
    }

    /**
     * @Route("/api/users/{user}/change_activity", name="api_users_change_active", methods={"POST"})
     * @param Request $request
     * @return JsonResponse|Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function changeUserActivity(User $user, Request $request, UpdatingUser $updatingUser)
    {
        $active = $this->getRequestParam($request, 'active');

        try {
            $resultUser = $updatingUser->setUser($user)->setActive($active)->execute();
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        return $this->response(['user' => $resultUser]);
    }

    /**
     * @Route("/api/users/{user}/change_custom_name", name="api_users_change_custom_name", methods={"POST"})
     * @param Request $request
     * @return JsonResponse|Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function changeUserCustomName(User $user, Request $request, UpdatingUser $updatingUser)
    {
        $customName = $this->getRequestParam($request, 'custom_name');
        $customName = trim($customName);

        try {
            $resultUser = $updatingUser->setUser($user)->setCustomName($customName)->execute();
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        return $this->response(['user' => $resultUser]);
    }

    /**
     * @Route("/api/users/remove", name="api_users_remove", methods={"POST"})
     * @param Request $request
     * @param RemovingUser $removingUser
     * @return JsonResponse|Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function removeUser(Request $request, RemovingUser $removingUser)
    {
        $userId = $this->getRequestParam($request, 'userId');

        try {
            $result = $removingUser->setUserId($userId)->execute();

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        return $this->response(['removed' => $result]);
    }

    /**
     * @Route("/api/users/grant_role", name="api_users_grant_role", methods={"POST"})
     * @param Request $request
     * @return JsonResponse|Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function grantRole(Request $request, GrantingUserRole $grantingUserRole)
    {
        $userId = $this->getRequestParam($request, 'user');
        $role = $this->getRequestParam($request, 'role');

        try {
            $user = $grantingUserRole->setUserId($userId)->setRole($role)->execute();

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        return $this->response(['user' => $user]);
    }

    /**
     * @Route("/api/users/revoke_role", name="api_users_revoke_role", methods={"POST"})
     * @param Request $request
     * @return JsonResponse|Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function revokeRole(Request $request, RevokingUserRole $revokingUserRole)
    {
        $userId = $this->getRequestParam($request, 'user');
        $role = $this->getRequestParam($request, 'role');

        try {
            $user = $revokingUserRole->setUserId($userId)->setRole($role)->execute();

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        return $this->response(['user' => $user]);
    }
}
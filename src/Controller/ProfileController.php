<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends BaseController
{

    /**
     * @Route("/api/profile", name="getAuthUser")
     * @return JsonResponse
     */
    public function index() {

        $user = $this->getUser();

        return $this->response(
            [ 'user' => $user ],
            [ 'attributes' => [ 'id', 'steamId64', 'profileName', 'profileUrl', 'avatar', 'roles', 'admin', 'gifter' ] ]
        );

    }

}
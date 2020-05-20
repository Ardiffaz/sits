<?php

namespace App\Controller;

use App\Operation\Giveaway\FinishingGiveaway;
use App\Operation\Giveaway\GettingGiveawayList;
use App\Operation\Giveaway\RemovingGiveaway;
use App\Operation\Giveaway\SavingGiveaway;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GiveawayController extends BaseController
{
    /**
     * @Route("/api/giveaways/", name="api_giveaways")
     * @param Request $request
     * @param GettingGiveawayList $gettingGiveawayList
     * @return JsonResponse|Response
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_GIFTER')")
     */
    public function getGiveawayList(Request $request, GettingGiveawayList $gettingGiveawayList)
    {
        $filter = array_intersect_key(
            $request->query->all(),
            array_fill_keys(
                ['link', 'creator', 'finished', 'game', 'key', 'notes'],
                1)
        );

        $pageNumber = (int)$request->get('page');
        if ($pageNumber <= 0)
            $pageNumber = 1;

        $sortParam = (string)$request->get('sort');
        $sortDir = (string)$request->get('dir');

        try {
            $giveawaysResult = $gettingGiveawayList
                ->setPageNumber($pageNumber)
                ->setFilter($filter)
                ->setSortParam($sortParam)
                ->setSortDir($sortDir)
                ->execute();
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        return $this->response(
            $this->getRefsFromGiveaways(
                    $giveawaysResult['giveaways'])
                        +
                    [
                        'curPageNumber' => $giveawaysResult['curPageNumber'],
                        'maxPageNumber' => $giveawaysResult['maxPageNumber'],
                        'totalItemsCount' => $giveawaysResult['totalItemsCount']
                    ],
            $this->getGiveawaysCallbackContext()
        );
    }


    /**
     * @Route("/api/giveaways/save", name="api_giveaways_save")
     * @param Request $request
     * @param SavingGiveaway $savingGiveaways
     * @return JsonResponse|Response
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_GIFTER')")
     */
    public function saveGiveaways(Request $request, SavingGiveaway $savingGiveaways)
    {
        $giveaways = $this->getRequestParam($request, 'giveaways');

        $errors = [];
        $createdGiveaways = [];

        foreach ($giveaways as $giveaway) {
            try {
                $createdGiveaway = $savingGiveaways->setGiveawayData($giveaway)->execute();
                $createdGiveaways[] = $createdGiveaway;
            } catch (Exception $e) {
                $errors[ $giveaway['id'] ] = $e->getMessage();
            }
        }

        return $this->response(
            $this->getRefsFromGiveaways($createdGiveaways) + ['errors' => $errors],
            $this->getGiveawaysCallbackContext()
        );
    }

    /**
     * @Route("/api/giveaways/finish", name="api_giveaways_finish")
     * @param Request $request
     * @param FinishingGiveaway $finishingGiveaway
     * @return JsonResponse|Response
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_GIFTER')")
     */
    public function finishGiveaway(Request $request, FinishingGiveaway $finishingGiveaway)
    {
        $giveawayId = $this->getRequestParam($request, 'giveawayId');

        try {
            $giveaway = $finishingGiveaway->setGiveawayId($giveawayId)->execute();
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        return $this->response(
            $this->getRefsFromGiveaways([$giveaway]),
            $this->getGiveawaysCallbackContext()
        );
    }

    /**
     * @Route("/api/giveaways/remove", name="api_giveaways_remove")
     * @param Request $request
     * @param RemovingGiveaway $removingGiveaway
     * @return JsonResponse|Response
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_GIFTER')")
     */
    public function removeGiveaway(Request $request, RemovingGiveaway $removingGiveaway)
    {
        $giveawayId = $this->getRequestParam($request, 'giveawayId');
        try {
            $gifts = $removingGiveaway->setGiveawayId($giveawayId)->execute();
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        return $this->response(
            $this->getRefsFromGifts($gifts),
            $this->getGiveawaysCallbackContext()
        );
    }
}
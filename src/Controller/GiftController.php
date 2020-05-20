<?php

namespace App\Controller;

use App\Operation\Gift\GettingGiftList;
use App\Operation\Gift\RemovingGift;
use App\Operation\Gift\SavingGifts;
use App\Operation\Gift\ReservingGift;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GiftController extends BaseController
{
    /**
     * @Route("/api/gifts/save", name="api_save_gifts", methods={"POST"})
     * @param Request $request
     * @param SavingGifts $savingGifts
     * @return JsonResponse|Response
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_GIFTER')")
     */
    public function saveGifts(Request $request, SavingGifts $savingGifts)
    {
        $gifts = $this->getRequestParam($request, 'gifts');
        $savedGifts = [];
        $errors = [];

        foreach ($gifts as $gift) {
            try {
                $gifts = $savingGifts->setGiftData($gift)->execute();
                $savedGifts = array_merge($savedGifts, $gifts);
            } catch (Exception $e) {
                $errors[ $gift['id'] ] = $e->getMessage();
            }
        }

        return $this->response(
            $this->getRefsFromGifts($savedGifts) + ['errors' => $errors],
            $this->getGiftsCallbackContext()
        );
    }

    /**
     * @Route("api/gifts", name="api_gifts")
     * @param Request $request
     * @param GettingGiftList $gettingGiftList
     * @return JsonResponse|Response
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_GIFTER')")
     */
    public function getList(Request $request, GettingGiftList $gettingGiftList)
    {
        $filter = array_intersect_key(
            $request->query->all(),
            array_fill_keys(
                ['key', 'reserved', 'source', 'lts', 'notes', 'exnotes', 'quality', 'cards', 'achievements', 'game', 'price_from', 'price_to'],
            1)
        );

        $pageNumber = (int)$request->get('page');
        if ($pageNumber <= 0)
            $pageNumber = 1;

        $sortParam = (string)$request->get('sort');
        $sortDir = (string)$request->get('dir');

        try {
            $giftsResult = $gettingGiftList
                ->setFilter($filter)
                ->setPageNumber($pageNumber)
                ->setSortParam($sortParam)
                ->setSortDir($sortDir)
                ->setTypeFree()
                ->execute();
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        return $this->response(
            $this->getRefsFromGifts($giftsResult['gifts']) + ['curPageNumber' => $giftsResult['curPageNumber'], 'maxPageNumber' => $giftsResult['maxPageNumber']],
            $this->getGiftsCallbackContext()
        );
    }

    /**
     * @Route("/api/gifts/reserve", name="api_reserve_gifts", methods={"POST"})
     * @param Request $request
     * @param ReservingGift $reservingGift
     * @return JsonResponse|Response
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_GIFTER')")
     */
    public function reserveGifts(Request $request, ReservingGift $reservingGift)
    {
        $giftIds = $this->getRequestParam($request, 'giftIds');
        $userId = $this->getRequestParam($request, 'userId');
        $isReserving = $this->getRequestParam($request, 'isReserving');
        $gifts = [];
        $errors = [];

        foreach ($giftIds as $giftId) {
            try {
                $gift = $reservingGift->setUserId($userId)->setGiftId($giftId)->setIsReserving($isReserving)->execute();
                $gifts[] = $gift;
            } catch (Exception $e) {
                $errors[$giftId] = $e->getMessage();
            }
        }

        return $this->response(
            $this->getRefsFromGifts($gifts) + ['errors' => $errors],
            $this->getGiftsCallbackContext()
        );
    }

    /**
     * @Route("/api/gifts/remove", name="api_remove_gifts", methods={"POST"})
     * @param Request $request
     * @param RemovingGift $removingGift
     * @return JsonResponse|Response
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_GIFTER')")
     */
    public function removeGifts(Request $request, RemovingGift $removingGift)
    {
        $giftIds = $this->getRequestParam($request, 'giftIds');
        $removedGifts = [];
        $errors = [];

        foreach ($giftIds as $giftId) {
            try {
                $removingGift->setGiftId($giftId)->execute();
                $removedGifts[] = $giftId;
            } catch (Exception $e) {
                $errors[$giftId] = $e->getMessage();
            }
        }

        return $this->response(['removed' => $removedGifts, 'errors' => $errors]);
    }
}
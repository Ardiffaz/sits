<?php

namespace App\Controller;


use App\Entity\Game;
use App\Entity\Gift;
use App\Entity\Giveaway;
use App\Entity\User;
use App\Services\ReferenceExtractor;
use App\Services\ReferencesView;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class BaseController extends AbstractController
{
    /** @var SerializerInterface */
    protected $serializer;

    protected $referenceExtractor;

    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
        return $this;
    }

    public function __construct(ReferenceExtractor $referenceExtractor)
    {
        $this->referenceExtractor = $referenceExtractor;
    }

    protected function getUser()
    {
        $user = parent::getUser();

        return $user;
    }

    /**
     * @param $data
     * @return JsonResponse
     */
    protected function apiResponse($data)
    {
        return new JsonResponse( array_merge(['success' => true], $data) );
    }


    /**
     * @param string|array $error
     * @return JsonResponse
     */
    protected function errorResponse($error)
    {
        return new JsonResponse( ['success' => false, 'error' => $error], 400 );
    }

    /**
     * @param string[] $data
     * @param array $context
     * @return JsonResponse|Response
     */
    protected function response(array $data, $context = [])
    {
        $basicContext = [
            'groups' => 'export',
            'circular_reference_handler' => function ($object) {
                /** @noinspection PhpUndefinedMethodInspection */
                return $object->getId();
            },
        ];

        $json = $this->serializer->serialize(
            $data,
            'json',
            $context + $basicContext
        );

        $response = new JsonResponse();
        $response->setContent( $json );

        return $response;
    }

    /**
     * @param Request $request
     * @param string $paramName
     * @return mixed
     */
    protected function getRequestParam(Request $request, string $paramName)
    {
        $data = json_decode($request->getContent(), true);
        $paramValue = $data[ $paramName ];
        return $paramValue;
    }


    protected function getGiftsCallbackContext()
    {
        $getUserId = function (?User $user) {
            return $user ? $user->getId() : null;
        };

        return [
            AbstractNormalizer::CALLBACKS => [
                'user' => $getUserId,
                'reservedBy' => $getUserId,
                'game' => function (Game $game) {
                    return $game->getId();
                },
                'giveaway' => function (?Giveaway $giveaway) {
                    return $giveaway ? $giveaway->getId() : null;
                },
                'gifts' => function ($gifts) {
                    $giftIds = [];
                    /** @var Gift $gift */
                    foreach ($gifts as $gift) {
                        $giftIds[] = $gift->getId();
                    }
                    return $giftIds;
                },
            ],
        ];
    }

    protected function getGiveawaysCallbackContext()
    {
        return self::getGiftsCallbackContext();
    }

    protected function getRefsFromGiveaways(iterable $giveaways)
    {
        $refs = $this->referenceExtractor->extract($giveaways, [
            Gift::class => [
                'reservedBy',
                'giveaway',
                'game',
            ],
            Giveaway::class => [
                'user',
                'gifts',
            ],
        ]);

        $builtRefs = ReferencesView::from($refs)->build();

        $addedGiveawayIds = [];
        /** @var Giveaway $giveaway */
        foreach ($giveaways as $giveaway) {
            $addedGiveawayIds[] = $giveaway->getId();
        }

        if (isset($builtRefs['giveaways']))
        {
            foreach ($builtRefs['giveaways'] as $key => $giveaway) {
                if (in_array($giveaway->getId(), $addedGiveawayIds))
                    unset($builtRefs['giveaways'][$key]);
            }
        }

        return array_merge_recursive(['giveaways' => $giveaways], $builtRefs );
    }

    protected function getRefsFromGifts(iterable $gifts) {
        $refs = $this->referenceExtractor->extract($gifts, [
            Gift::class => [
                'reservedBy',
                'giveaway',
                'game',
            ],
            Giveaway::class => [
                'user',
                //'gifts'
            ],
        ]);

        return array_merge_recursive(ReferencesView::from($refs)->build(), ['gifts' => $gifts]);
    }
}
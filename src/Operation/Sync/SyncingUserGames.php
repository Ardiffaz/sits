<?php

namespace App\Operation\Sync;

use App\Entity\User;
use App\Operation\BaseOperation;
use App\Tools\SteamApi\ApiClient;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Serializer\SerializerInterface;

class SyncingUserGames extends BaseOperation
{
    /** @var User */
    protected $user;

    /** @var ApiClient */
    protected $apiClient;

    /** @var SyncingUserOwnedGames */
    protected $syncingUserOwnedGames;

    /** @var SyncingUserWishlistedGames */
    protected $syncingUserWishlistedGames;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ApiClient $apiClient,
        SyncingUserOwnedGames $syncingUserOwnedGames,
        SyncingUserWishlistedGames $syncingUserWishlistedGames
    )
    {
        parent::__construct($entityManager, $serializer);
        $this->apiClient = $apiClient;
        $this->syncingUserOwnedGames = $syncingUserOwnedGames;
        $this->syncingUserWishlistedGames = $syncingUserWishlistedGames;
    }

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @param $userId
     * @return SyncingUserGames
     * @throws Exception
     */
    public function setUserId($userId)
    {
        $user = $this->em->getRepository(User::class)->find($userId);

        if (!$user)
            throw new Exception(self::class.': User id#'.$userId.' not found');

        $this->setUser($user);
        return $this;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function execute()
    {
        if (!$this->user)
            throw new Exception(self::class.': User is not set');

        $counters = [];

        $counters['owned'] = $this->syncingUserOwnedGames->setUser($this->user)->execute();
        $counters['wishlisted'] = $this->syncingUserWishlistedGames->setUser($this->user)->execute();

        return $counters;
    }
}
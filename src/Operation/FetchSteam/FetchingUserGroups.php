<?php

namespace App\Operation\FetchSteam;

use App\Operation\BaseOperation;
use App\Tools\SteamApi\ApiClient;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Serializer\SerializerInterface;

class FetchingUserGroups extends BaseOperation
{
    protected $userSteamId64;

    /** @var ApiClient */
    protected $apiClient;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer, ApiClient $apiClient)
    {
        parent::__construct($entityManager, $serializer);
        $this->apiClient = $apiClient;
    }

    public function setUserSteamId64($userSteamId64)
    {
        $this->userSteamId64 = $userSteamId64;
        return $this;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function execute()
    {
        if (!$this->userSteamId64)
            throw new Exception(self::class.': userSteamId64 is not set');

        return $this->apiClient->getUserGroupList($this->userSteamId64);
    }
}
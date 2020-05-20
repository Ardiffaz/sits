<?php

namespace App\Operation\FetchSteam;

use App\Operation\BaseOperation;
use App\Tools\SteamApi\ApiClient;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Serializer\SerializerInterface;

class FetchingUser extends BaseOperation
{
    protected $userSteamId;

    /** @var ApiClient */
    protected $apiClient;

    public function __construct(ApiClient $apiClient, EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        parent::__construct($entityManager, $serializer);
        $this->apiClient = $apiClient;
    }

    public function setUserId($userSteamId)
    {
        $this->userSteamId = $userSteamId;
        return $this;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function execute()
    {
        if (!$this->userSteamId)
            throw new Exception(self::class.': UserSteamId is not set');

        return $this->apiClient->getPlayerSummaries($this->userSteamId);
    }
}
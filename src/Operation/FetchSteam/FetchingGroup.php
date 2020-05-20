<?php

namespace App\Operation\FetchSteam;

use App\Operation\BaseOperation;
use App\Tools\SteamApi\ApiClient;
use App\Tools\SteamApi\Object\Group;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Serializer\SerializerInterface;

class FetchingGroup extends BaseOperation
{
    protected $groupId64;

    /** @var ApiClient */
    protected $apiClient;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer, ApiClient $apiClient)
    {
        parent::__construct($entityManager, $serializer);
        $this->apiClient = $apiClient;
    }

    public function setGroupId64($groupId64)
    {
        $this->groupId64 = $groupId64;
        return $this;
    }

    /**
     * @return Group
     * @throws Exception
     */
    public function execute()
    {
        if (!$this->groupId64)
            throw new Exception(self::class.': GroupId is not set');

        return $this->apiClient->getGroupSummary($this->groupId64);
    }
}
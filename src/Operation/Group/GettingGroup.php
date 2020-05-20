<?php

namespace App\Operation\Group;

use App\Entity\Group;
use App\Operation\BaseOperation;
use Exception;

class GettingGroup extends BaseOperation
{
    protected $groupId;

    /**
     * @param $groupId
     * @return $this
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
        return $this;
    }

    /**
     * @return Group|object|null
     * @throws Exception
     */
    public function execute()
    {
        if (!$this->groupId)
            throw new Exception(self::class.': Group Id not set.');

        $group = $this->em->getRepository(Group::class)->find($this->groupId);

        return $group;
    }
}
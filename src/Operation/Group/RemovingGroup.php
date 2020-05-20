<?php

namespace App\Operation\Group;

use App\Entity\Group;
use App\Operation\BaseOperation;
use Exception;

class RemovingGroup extends BaseOperation
{
    /** @var Group */
    protected $group;

    /**
     * @param int $groupId
     * @return $this
     * @throws Exception
     */
    public function setGroupId(int $groupId)
    {
        $group = $this->em->getRepository(Group::class)->find( $groupId );

        if (!$group)
            throw new Exception(self::class.': Group id#'.$groupId.' not found');

        $this->group = $group;
        return $this;
    }

    public function setGroup($group)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function execute()
    {
        if (!$this->group)
            throw new Exception(self::class.': Group not set.');

        $this->em->remove($this->group);
        $this->em->flush();

        return true;
    }
}
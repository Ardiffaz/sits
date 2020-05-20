<?php

namespace App\Operation\Group;

use App\Entity\Group;
use App\Operation\BaseOperation;
use Exception;

class UpdatingGroup extends BaseOperation
{
    /** @var Group */
    protected $group;

    protected $sgLink;

    public function setGroup(Group $group)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @param $groupId
     * @return $this
     * @throws Exception
     */
    public function setGroupId($groupId)
    {
        $group = $this->em->getRepository(Group::class)->find( $groupId );

        if (!$group)
            throw new Exception(self::class.': Group id#'.$groupId.' not found');

        $this->group = $group;
        return $this;
    }

    public function setSgLink(string $sgLink)
    {
        $this->sgLink = $sgLink;
        return $this;
    }

    /**
     * @return Group
     * @throws Exception
     */
    public function execute()
    {
        if (!$this->group)
            throw new Exception(self::class.': Group not set.');

        if ($this->sgLink)
            $this->group->setSgLink($this->sgLink);

        $this->em->flush();

        return $this->group;
    }
}
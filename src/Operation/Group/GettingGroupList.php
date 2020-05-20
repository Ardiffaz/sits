<?php

namespace App\Operation\Group;

use App\Entity\Group;
use App\Operation\BaseOperation;

class GettingGroupList extends BaseOperation
{
    /**
     * @return Group[]
     */
    public function execute()
    {
        $groups = $this->em->createQueryBuilder()
            ->from(Group::class, 'g')
            ->select('g')
            ->orderBy('g.name')
            ->leftJoin('g.members', 'members')
            ->getQuery()
            ->getResult();

        return $groups;
    }
}
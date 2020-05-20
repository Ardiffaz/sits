<?php

namespace App\Operation\User;

use App\Entity\Group;
use App\Entity\OwnedGame;
use App\Entity\User;
use App\Entity\WishlistedGame;
use App\Operation\BaseOperation;
use Exception;

class GettingUserList extends BaseOperation
{
    public const TYPE_GIFTERS = 'gifters';
    public const TYPE_ROLES = 'with_roles';

    /** @var Group */
    protected $group;

    /** @var bool */
    protected $giftersOnly = false;

    /** @var string */
    protected $type = '';

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

    public function setGroup($group)
    {
        $this->group = $group;
        return $this;
    }

    public function setTypeGifters()
    {
        $this->type = self::TYPE_GIFTERS;
        return $this;
    }

    public function setTypeWithRoles()
    {
        $this->type = self::TYPE_ROLES;
        return $this;
    }

    /**
     * @return User[]
     * @throws Exception
     */
    public function execute()
    {

        $queryBuilder = $this->em->createQueryBuilder()
            ->from(User::class, 'u')
            ->select('u')
            ->addOrderBy('u.profileName', 'ASC');

        if (isset($this->group))
        {
            $queryBuilder
                ->leftJoin('u.groups', 'ug')
                ->andWhere('ug = :group')
                ->setParameter('group', $this->group);
        }

        if ($this->type === self::TYPE_GIFTERS)
        {
            $queryBuilder
                ->andWhere("u.roles LIKE '%".User::ROLE_GIFTER."%'");
        }

        if ($this->type === self::TYPE_ROLES)
        {
            $queryBuilder
                ->andWhere("concat(u.roles, '') != '[]' ");
        }

        /** @var User[] $users */
        $users = $queryBuilder
            ->getQuery()
            ->getResult();

        // owned and wishlisted games count
        $userCounters = [];

        $ownedData = $this->em->createQueryBuilder()
            ->from(OwnedGame::class, 'og')
            ->andWhere('og.user in (:users)')
            ->setParameter('users', $users)
            ->select('count(og.id) as count', 'identity(og.user) as userId')
            ->groupBy('og.user')
            ->getQuery()
            ->getArrayResult();

        foreach ($ownedData as $ownedDatum) {
            $userCounters[ $ownedDatum['userId'] ]['owned'] = $ownedDatum['count'];
        }

        $wishlistedData = $this->em->createQueryBuilder()
            ->from(WishlistedGame::class, 'wg')
            ->andWhere('wg.user in (:users)')
            ->setParameter('users', $users)
            ->select('count(wg.id) as count', 'identity(wg.user) as userId')
            ->groupBy('wg.user')
            ->getQuery()
            ->getArrayResult();

        foreach ($wishlistedData as $wishlistedDatum) {
            $userCounters[ $wishlistedDatum['userId'] ]['wishlisted'] = $wishlistedDatum['count'];
        }

        foreach ($users as $user) {
            $userCounter = isset($userCounters[ $user->getId() ]) ? $userCounters[ $user->getId() ] : [];

            if (isset($userCounter['owned']))
                $user->setOwnedCount( $userCounter['owned'] );

            if (isset($userCounter['wishlisted']))
                $user->setWishlistedCount( $userCounter['wishlisted'] );
        }

        return $users;
    }
}
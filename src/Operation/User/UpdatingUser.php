<?php

namespace App\Operation\User;

use App\Entity\User;
use App\Operation\BaseOperation;
use Exception;

class UpdatingUser extends BaseOperation
{
    /** @var User */
    protected $user;

    protected $active;
    protected $customName;

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @param $userId
     * @return $this
     * @throws Exception
     */
    public function setUserId($userId)
    {
        /** @var User $user */
        $user = $this->em->getRepository(User::class)->find($userId);
        if (!$user)
            throw new Exception(self::class.': User id#'.$userId.' not found');

        return $this->setUser($user);
    }

    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }

    public function setCustomName($customName)
    {
        $this->customName = $customName;
        return $this;
    }

    /**
     * @return User
     * @throws Exception
     */
    public function execute()
    {
        if (!$this->user)
            throw new Exception(self::class.': User is not set.');

        if (isset($this->active))
            $this->user->setActiveInGroups( $this->active );

        if (isset($this->customName))
            $this->user->setCustomName( $this->customName );

        $this->em->flush();

        return $this->user;
    }
}
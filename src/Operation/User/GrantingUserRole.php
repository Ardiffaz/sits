<?php

namespace App\Operation\User;

use App\Entity\User;
use App\Operation\BaseOperation;
use Exception;

class GrantingUserRole extends BaseOperation
{
    /** @var User */
    protected $user;

    /** @var string */
    protected $role;

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    public function setRole(string $role)
    {
        if ($role === 'admin')
            $this->role = User::ROLE_ADMIN;
        elseif ($role === 'gifter')
            $this->role = User::ROLE_GIFTER;

        return $this;
    }

    /**
     * @param $userId
     * @return GrantingUserRole
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

    /**
     * @return User
     * @throws Exception
     */
    public function execute()
    {
        if (!$this->user)
            throw new Exception(self::class.': User is not set.');

        if (!$this->role)
            throw new Exception(self::class.': User Role is not selected.');

        $this->user->addRole($this->role);

        $this->em->flush();

        return $this->user;
    }
}
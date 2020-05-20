<?php

namespace App\Operation\User;

use App\Entity\OwnedGame;
use App\Entity\User;
use App\Entity\WishlistedGame;
use App\Operation\BaseOperation;
use Exception;

class RemovingUser extends BaseOperation
{
    /** @var User */
    protected $user;

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
     * @return RemovingUser
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
     * @return bool
     * @throws Exception
     */
    public function execute()
    {
        if (!$this->user)
            throw new Exception(self::class.': User is not set.');

        $ownedGames = $this->em->createQueryBuilder()
            ->from(OwnedGame::class, 'og')
            ->select('og')
            ->andWhere('og.user = :user')
            ->setParameter('user', $this->user)
            ->getQuery()
            ->getResult();

        foreach ($ownedGames as $ownedGame)
        {
            $this->em->remove($ownedGame);
        }

        $wishlistedGames = $this->em->createQueryBuilder()
            ->from(WishlistedGame::class, 'wg')
            ->select('wg')
            ->andWhere('wg.user = :user')
            ->setParameter('user', $this->user)
            ->getQuery()
            ->getResult();

        foreach ($wishlistedGames as $wishlistedGame) {
            $this->em->remove($wishlistedGame);
        }
        $this->em->flush();

        $this->em->remove($this->user);
        $this->em->flush();

        return true;
    }

}
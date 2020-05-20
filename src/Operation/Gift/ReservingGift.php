<?php

namespace App\Operation\Gift;

use App\Entity\Gift;
use App\Entity\User;
use App\Operation\BaseOperation;
use Exception;

class ReservingGift extends BaseOperation
{
    /** @var Gift */
    protected $gift;

    /** @var User */
    protected $user;

    /** @var bool */
    protected $isReserving = true;

    public function setGift(Gift $gift)
    {
        $this->gift = $gift;
        return $this;
    }

    /**
     * @param int $giftId
     * @return $this
     * @throws Exception
     */
    public function setGiftId(int $giftId)
    {
        /** @var Gift $gift */
        $gift = $this->em->getRepository(Gift::class)->find( $giftId );

        if (!$gift)
            throw new Exception(self::class.': Gift id#'.$giftId.' not found');

        $this->setGift($gift);
        return $this;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
        return  $this;
    }

    /**
     * @param int|null $userId
     * @return $this
     * @throws Exception
     */
    public function setUserId(?int $userId)
    {
        if (!$userId)
            throw new Exception(self::class.': user not defined');

        /** @var User $user */
        $user = $this->em->getRepository(User::class)->find( $userId );

        if (!$user)
            throw new Exception(self::class.': Gift id#'.$userId.' not found');

        $this->setUser($user);
        return $this;
    }

    public function setIsReserving(bool $isReserving)
    {
        $this->isReserving = $isReserving;
        return $this;
    }

    /**
     * @return Gift
     * @throws Exception
     */
    public function execute()
    {
        if (!$this->gift)
            throw new Exception(self::class.': Gift not set.');

        if ($this->gift->getGiveaway())
            throw new Exception(self::class.': Cannot reserve already given gift.');

        $giftIsFree = $this->gift->getReservedBy() === null;

        if ($this->isReserving && !$giftIsFree)
        {
            $reservedBy = $this->gift->getReservedBy();

            if ($reservedBy->getId() !== $this->user->getId())
                throw new Exception('This gift is already selected by '.$reservedBy->getProfileName() );
        }

        if (!$this->user)
            throw new Exception(self::class.': User not set.');

        if ($this->isReserving && $giftIsFree)
        {
            $this->gift->setReservedBy($this->user);
        }

        if (!$this->isReserving && $this->gift->getReservedBy() === $this->user)
        {
            $this->gift->setReservedBy(null);
        }

        $this->em->flush();
        return $this->gift;
    }
}
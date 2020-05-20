<?php

namespace App\Operation\Gift;

use App\Entity\Gift;
use App\Operation\BaseOperation;
use Exception;

class RemovingGift extends BaseOperation
{
    /** @var Gift */
    protected $gift;

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
        $gift = $this->em->getRepository(Gift::class)->find( $giftId );

        if (!$gift)
            throw new Exception(self::class.': Gift id#'.$giftId.' not found');

        $this->gift = $gift;
        return $this;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function execute()
    {
        if (!$this->gift)
            throw new Exception(self::class.': Gift not set');

        if ($this->gift->getGiveaway())
            throw new Exception('Cannot remove a gift which has a giveaway connected.');

        $this->em->remove($this->gift);
        $this->em->flush();

        return true;
    }
}
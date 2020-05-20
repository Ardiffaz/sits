<?php

namespace App\Operation\Giveaway;

use App\Entity\Giveaway;
use App\Operation\BaseOperation;
use Exception;

class FinishingGiveaway extends BaseOperation
{
    /** @var Giveaway */
    protected $giveaway;

    public function setGiveaway(Giveaway $giveaway)
    {
        $this->giveaway = $giveaway;
        return $this;
    }

    /**
     * @param int $giveawayId
     * @return $this
     * @throws Exception
     */
    public function setGiveawayId(int $giveawayId)
    {
        /** @var Giveaway $giveaway */
        $giveaway = $this->em->getRepository(Giveaway::class)->find($giveawayId);

        if (!$giveaway)
            throw new Exception(self::class.': Giveaway id#'.$giveawayId.' not found');

        $this->setGiveaway($giveaway);
        return $this;
    }

    /**
     * @return Giveaway
     * @throws Exception
     */
    public function execute()
    {
        if (!$this->giveaway)
            throw new Exception(self::class.': Giveaway not set.');

        $this->giveaway->setSuccessful(true);
        $this->em->flush();
        return $this->giveaway;
    }
}
<?php

namespace App\Operation\Giveaway;

use App\Entity\Gift;
use App\Entity\Giveaway;
use App\Entity\User;
use App\Operation\BaseOperation;
use Exception;

class SavingGiveaway extends BaseOperation
{
    /** @var array */
    protected $giveawayData;

    public function setGiveawayData(array $giveawayData)
    {
        $this->giveawayData = $giveawayData;
        return $this;
    }

    /**
     * @return Giveaway
     * @throws Exception
     */
    public function execute()
    {
        /** @var User $user */
        $user = $this->em->getRepository(User::class)->find($this->giveawayData['user']);

        if (!$user)
        {
            throw new Exception('User id#'.$this->giveawayData['user'].' not found');
        }

        $giveawayId = isset($this->giveawayData['id']) ? $this->giveawayData['id'] : '';
        $existedGiveaway = null;

        if ($giveawayId && is_int($giveawayId))
        {
            /** @var Giveaway $existedGiveaway */
            $existedGiveaway = $this->em->getRepository(Giveaway::class)->find($giveawayId);

            if (!$existedGiveaway)
                throw new Exception(self::class.': giveaway id#'.$giveawayId.' not found.');
        }

        $giftIds = $this->giveawayData['gifts'];

        $gifts = $this->em->createQueryBuilder()
            ->from(Gift::class, 'gift')
            ->select('gift')
            ->andWhere('gift.id in (:giftIds)')
            ->setParameter('giftIds', $giftIds)
            ->getQuery()
            ->getResult();

        /** @var Gift[] $gifts */
        $gifts = array_combine(
            array_map(function(Gift $gift) {
                return $gift->getId();
            }, $gifts),
            $gifts
        );

        $notFoundGiftIds = array_diff($giftIds, array_keys($gifts));

        if ( count($notFoundGiftIds) > 0 )
        {
            throw new Exception('Gift id#'.implode(', ', $notFoundGiftIds).' not found');
        }

        $giftedGameIds = [];

        foreach ($gifts as $gift) {
            if (!$existedGiveaway && $gift->getGiveaway())
                throw new Exception('Gift id'.$gift->getId().' already has a giveaway attached.');

            if ( $gift->getReservedBy() && ($gift->getReservedBy()->getId() !== $user->getId()) )
                throw new Exception('Gift id'.$gift->getId().' is reserved by another user: '.$gift->getReservedBy()->getProfileName());

            $gameId = $gift->getGame()->getId();

            if (!in_array($gameId, $giftedGameIds))
                $giftedGameIds[] = $gameId;
        }

        if (count($giftedGameIds) > 1)
        {
            throw new Exception(
                'Selected gifts should refer to the same game. Current list of games: '
                    .implode(', ', $giftedGameIds).'.'
            );
        }

        // edit existed or create a new one
        $giveaway = $existedGiveaway ? $existedGiveaway : new Giveaway();

        $notes = ' '.trim($this->giveawayData['notes']).' ';

        $giveaway->setLink($this->giveawayData['link'])
            ->setUser($user)
            ->setDateEnded( \DateTime::createFromFormat('Y-m-d', $this->giveawayData['dateEnded']) )
            ->setSuccessful($this->giveawayData['successful'])
            ->setNotes($notes);


        if ($existedGiveaway)
        {
            foreach ($giveaway->getGifts() as $gift) {
                // set the gift free if it's not in array from request
                if (!in_array($gift->getId(), $giftIds))
                    $gift->clearGiveaway();
            }
        }

        $giveaway->setGifts($gifts);
        $this->em->persist($giveaway);

        foreach ($gifts as $gift) {
            $gift->setGiveaway($giveaway);
            $gift->clearReservedBy();
        }

        $this->em->flush();
        return $giveaway;

    }
}
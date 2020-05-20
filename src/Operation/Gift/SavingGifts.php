<?php

namespace App\Operation\Gift;

use App\Entity\Game;
use App\Entity\Gift;
use App\Operation\BaseOperation;
use Exception;

class SavingGifts extends BaseOperation
{
    /** @var array */
    protected $giftData;

    public function setGiftData(array $giftData)
    {
        $this->giftData = $giftData;
        return $this;
    }

    /**
     * @return Gift[]
     * @throws Exception
     */
    public function execute()
    {
        if (!$this->giftData)
            throw new Exception(self::class.': gift data not set');

        if (!strlen($this->giftData['key']))
            throw new Exception('Gift key cannot be empty.');

        $game = null;

        if (isset($this->giftData['game']) && $this->giftData['game'])
        {
            $gameId = $this->giftData['game'];
            /** @var Game $game */
            $game = $this->em->getRepository(Game::class)->find($gameId);

            if (!$game)
            {
                $game = new Game();
                $game->setId($gameId);

                $this->em->persist($game);
                $this->em->flush();

                //throw new Exception('Game id '.$this->giftData['game'].' not found');
            }

        }

        $newPrice = (float)$this->giftData['price'];

        $notes = ' '.trim($this->giftData['notes']).' ';

        $giftId = $this->giftData['id'];

        if ($giftId && is_int($giftId))
        {
            /** @var Gift $existedGift */
            $existedGift = $this->em->getRepository(Gift::class)->find( $giftId );

            if (!$existedGift)
                throw new Exception(self::class.': gift id#'.$giftId.' not found.');

            $existedGift->setKey($this->giftData['key'])
                ->setSourceName($this->giftData['sourceName'])
                ->setSourceLink($this->giftData['sourceLink'])
                ->setPrice($newPrice)
                ->setNotes($notes);

            if ($game)
                $existedGift->setGame($game);

            $this->em->flush();
            return [$existedGift];
        }

        if (!$game)
            throw new Exception('Game not selected.');

        $keys = explode("\n", $this->giftData['key']);
        $createdGifts = [];

        foreach ($keys as $key) {

            if (trim($key) === '')
                continue;

            $gift = new Gift();
            $gift->setKey( trim($key) )
                ->setSourceName($this->giftData['sourceName'])
                ->setSourceLink($this->giftData['sourceLink'])
                ->setPrice($newPrice)
                ->setGame($game)
                ->setNotes($notes);

            $this->em->persist($gift);

            $createdGifts[] = $gift;
        }

        $this->em->flush();
        return $createdGifts;
    }
}
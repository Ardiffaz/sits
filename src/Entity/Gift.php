<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="App\Repository\GiftRepository")
 * @ORM\Table("gifts")
 */
class Gift
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", nullable=false, unique=true)
     * @Groups({"export"})
     */
    private $id;

    /**
     * @var Game
     * @ORM\ManyToOne(targetEntity="Game", cascade={"persist"})
     * @ORM\JoinColumn(fieldName="game_id", referencedColumnName="id", nullable=false)
     * @Groups({"export"})
     */
    private $game;

    /**
     * @var string
     * @ORM\Column(type="string", name="`key`", nullable=false)
     * @Groups({"export"})
     */
    private $key;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"export"})
     */
    private $sourceName;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"export"})
     */
    private $sourceLink;

    /**
     * @var float
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"export"})
     */
    private $price;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"export"})
     */
    private $notes;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist"})
     * @ORM\JoinColumn(fieldName="reserved_by", referencedColumnName="id", nullable=true)
     * @Groups({"export"})
     */
    private $reservedBy;

    /**
     * @var Giveaway
     * @ORM\ManyToOne(targetEntity="Giveaway", inversedBy="gifts", cascade={"persist"})
     * @Groups({"export"})
     */
    private $giveaway;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Game
     */
    public function getGame(): Game
    {
        return $this->game;
    }

    /**
     * @param Game $game
     * @return Gift
     */
    public function setGame(Game $game): Gift
    {
        $this->game = $game;
        return $this;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return Gift
     */
    public function setKey(string $key): Gift
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getSourceName(): string
    {
        return $this->sourceName;
    }

    /**
     * @param string $sourceName
     * @return Gift
     */
    public function setSourceName(string $sourceName): Gift
    {
        $this->sourceName = $sourceName;
        return $this;
    }

    /**
     * @return string
     */
    public function getSourceLink(): string
    {
        return $this->sourceLink;
    }

    /**
     * @param string $sourceLink
     * @return Gift
     */
    public function setSourceLink(string $sourceLink): Gift
    {
        $this->sourceLink = $sourceLink;
        return $this;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     * @return Gift
     */
    public function setPrice(float $price): Gift
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return string
     */
    public function getNotes(): string
    {
        return $this->notes;
    }

    /**
     * @param string $notes
     * @return Gift
     */
    public function setNotes(string $notes): Gift
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * @return ?User
     */
    public function getReservedBy()
    {
        return $this->reservedBy;
    }

    /**
     * @param User|null $reservedBy
     * @return Gift
     */
    public function setReservedBy($reservedBy): Gift
    {
        $this->reservedBy = $reservedBy;
        return $this;
    }

    /**
     * @return Giveaway|null
     */
    public function getGiveaway()
    {
        return $this->giveaway;
    }

    /**
     * @param Giveaway $giveaway
     * @return Gift
     */
    public function setGiveaway(Giveaway $giveaway): Gift
    {
        $this->giveaway = $giveaway;
        return $this;
    }

    /**
     * @return Gift
     */
    public function clearGiveaway(): Gift
    {
        $this->giveaway = null;
        return $this;
    }

    /**
     * @return Gift
     */
    public function clearReservedBy(): Gift
    {
        $this->reservedBy = null;
        return $this;
    }
}
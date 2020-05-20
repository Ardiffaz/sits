<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="App\Repository\GiveawayRepository")
 * @ORM\Table(name="giveaways")
 */
class Giveaway
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", nullable=false, unique=true)
     * @Groups({"export"})
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"export"})
     */
    private $link;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"export"})
     */
    private $dateEnded;
    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false, options={"default": false})
     * @Groups({"export"})
     */
    private $successful = false;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist"})
     * @ORM\JoinColumn(fieldName="user_id", referencedColumnName="id", nullable=true)
     * @Groups({"export"})
     */
    private $user;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"export"})
     */
    private $notes;

    /**
     * @var Gift[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="Gift", mappedBy="giveaway", cascade={"persist"})
     * @Groups({"export"})
     */
    private $gifts;
    
    public function __construct()
    {
        $this->gifts = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Giveaway
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @param string $link
     * @return Giveaway
     */
    public function setLink(string $link): Giveaway
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateEnded(): DateTime
    {
        return $this->dateEnded;
    }

    /**
     * @param DateTime $dateEnded
     * @return Giveaway
     */
    public function setDateEnded(DateTime $dateEnded): Giveaway
    {
        $this->dateEnded = $dateEnded;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    /**
     * @param bool $successful
     * @return Giveaway
     */
    public function setSuccessful(bool $successful): Giveaway
    {
        $this->successful = $successful;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return Giveaway
     */
    public function setUser(User $user): Giveaway
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * @param string notes
     * @return Giveaway
     */
    public function setNotes(string $notes): Giveaway
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return Gift[]
     */
    public function getGifts()
    {
        return $this->gifts->toArray();
    }

    /**
     * @param Gift[] $gifts
     * @return Giveaway
     */
    public function setGifts(array $gifts): Giveaway
    {
        $this->gifts = $gifts;
        return $this;
    }

}
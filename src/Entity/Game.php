<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="App\Repository\GameRepository")
 * @ORM\Table(name="games", uniqueConstraints={
 *     @ORM\UniqueConstraint(columns={"id"})
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class Game
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(type="integer", nullable=false, unique=true)
     * @Groups({"export"})
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"export"})
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private $type;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"export"})
     */
    private $achievementCount;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", options={"default": false})
     * @Groups({"export"})
     */
    private $hasTradingCards = false;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"export"})
     */
    private $rating;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"export"})
     */
    private $reviewCount;

    /**
     * @var float|null
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"export"})
     */
    private $priceUsd;

    /**
     * @var float|null
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"export"})
     */
    private $priceRub;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", options={"default": false})
     * @Groups({"export"})
     */
    private $bundled = false;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastUpdated;

    /**
     * @var WishlistedGame[]
     * @ORM\OneToMany(targetEntity="WishlistedGame", mappedBy="game")
     */
    private $wishlistedGames;

    /**
     * @var OwnedGame[]
     * @ORM\OneToMany(targetEntity="OwnedGame", mappedBy="game")
     */
    private $ownedGames;

    /**
     * @var array|ArrayCollection
     * @Groups({"export-game-count"})
     */
    private $ownedBy;

    /**
     * @var array|ArrayCollection
     * @Groups({"export-game-count"})
     */
    private $wishlistedBy;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Game
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Game
     */
    public function setName(string $name): Game
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getType() : ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Game
     */
    public function setType(string $type): Game
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getAchievementCount(): ?int
    {
        return $this->achievementCount;
    }

    /**
     * @param int|null $achievementCount
     * @return Game
     */
    public function setAchievementCount(?int $achievementCount): Game
    {
        $this->achievementCount = $achievementCount;
        return $this;
    }

    /**
     * @return bool
     */
    public function isHasTradingCards(): bool
    {
        return $this->hasTradingCards;
    }

    /**
     * @param bool $hasTradingCards
     * @return Game
     */
    public function setHasTradingCards(bool $hasTradingCards): Game
    {
        $this->hasTradingCards = $hasTradingCards;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getRating(): ?int
    {
        return $this->rating;
    }

    /**
     * @param int|null $rating
     * @return Game
     */
    public function setRating(?int $rating): Game
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getReviewCount(): ?int
    {
        return $this->reviewCount;
    }

    /**
     * @param int|null $reviewCount
     * @return Game
     */
    public function setReviewCount(?int $reviewCount): Game
    {
        $this->reviewCount = $reviewCount;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getPriceUsd(): ?float
    {
        return $this->priceUsd;
    }

    /**
     * @param float|null $priceUsd
     * @return Game
     */
    public function setPriceUsd(?float $priceUsd): Game
    {
        $this->priceUsd = $priceUsd;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getPriceRub(): ?float
    {
        return $this->priceRub;
    }

    /**
     * @param float|null $priceRub
     * @return Game
     */
    public function setPriceRub(?float $priceRub): Game
    {
        $this->priceRub = $priceRub;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBundled(): bool
    {
        return $this->bundled;
    }

    /**
     * @param bool $bundled
     * @return Game
     */
    public function setBundled(bool $bundled)
    {
        $this->bundled = $bundled;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }

    /**
     * @param DateTime $lastUpdated
     * @return Game
     */
    public function setLastUpdated(DateTime $lastUpdated): Game
    {
        $this->lastUpdated = $lastUpdated;

        return $this;
    }

    /**
     * @return WishlistedGame[]
     */
    public function getWishlistedGames(): array
    {
        return $this->wishlistedGames;
    }

    /**
     * @param WishlistedGame[] $wishlistedGames
     * @return Game
     */
    public function setWishlistedGames(array $wishlistedGames): Game
    {
        $this->wishlistedGames = $wishlistedGames;
        return $this;
    }

    /**
     * @return OwnedGame[]
     */
    public function getOwnedGames(): array
    {
        return $this->ownedGames;
    }

    /**
     * @param OwnedGame[] $ownedGames
     * @return Game
     */
    public function setOwnedGames(array $ownedGames): Game
    {
        $this->ownedGames = $ownedGames;
        return $this;
    }

    /**
     * @return array
     */
    public function getOwnedBy()
    {
        return $this->ownedBy;
    }

    /**
     * @param array $ownedBy
     * @return Game
     */
    public function setOwnedBy(array $ownedBy): Game
    {
        $this->ownedBy->clear();
        foreach ($ownedBy as $userId) {
            $this->addOwnedBy($userId);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getWishlistedBy()
    {
        return $this->wishlistedBy;
    }

    /**
     * @param array $wishlistedBy
     * @return Game
     */
    public function setWishlistedBy(array $wishlistedBy): Game
    {
        $this->wishlistedBy->clear();
        foreach ($wishlistedBy as $userId) {
            $this->addWishlistedBy($userId);
        }

        return $this;
    }

    public function addWishlistedBy($userId)
    {
        if ($this->wishlistedBy === null)
            $this->wishlistedBy = new ArrayCollection();

        if ($this->wishlistedBy->contains($userId)) {
            return $this;
        }

        $this->wishlistedBy[] = $userId;
        return $this;
    }

    public function addOwnedBy($userId, $active = true)
    {
        if ($this->ownedBy === null)
            $this->ownedBy = new ArrayCollection();


        if ($this->ownedBy->containsKey($userId)) {
            return $this;
        }

        $this->ownedBy[$userId] = $active;
        return $this;
    }

    /**
     * Game constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * @ORM\PostLoad()
     */
    public function init()
    {
        $this->wishlistedBy = new ArrayCollection();
        $this->ownedBy = new ArrayCollection();
    }
}
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="App\Repository\OwnedGameRepository")
 * @ORM\Table(
 *     name="owned_games",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(columns={"game_id", "user_id"})
 *     },
 *     indexes={
 *          @ORM\Index(columns={"game_id"}),
 *          @ORM\Index(columns={"user_id"}),
 *          @ORM\Index(columns={"game_id", "user_id"})
 *     }
 * )
 */
class OwnedGame
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Game
     * @ORM\ManyToOne(targetEntity="Game", cascade={"persist"}, inversedBy="ownedGames")
     * @ORM\JoinColumn(fieldName="game_id", referencedColumnName="id", nullable=false)
     */
    private $game;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist"})
     * @ORM\JoinColumn(fieldName="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $active = true;

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
     * @return OwnedGame
     */
    public function setGame(Game $game): OwnedGame
    {
        $this->game = $game;
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
     * @return OwnedGame
     */
    public function setUser(User $user): OwnedGame
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return OwnedGame
     */
    public function setActive(bool $active): OwnedGame
    {
        $this->active = $active;
        return $this;
    }

}
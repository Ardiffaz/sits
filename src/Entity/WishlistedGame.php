<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="App\Repository\WishlistedGameRepository")
 * @ORM\Table(
 *     name="wishlisted_games",
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
class WishlistedGame
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Game
     * @ORM\ManyToOne(targetEntity="Game", cascade={"persist"}, inversedBy="wishlistedGames")
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
     * @return WishlistedGame
     */
    public function setGame(Game $game): WishlistedGame
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
     * @return WishlistedGame
     */
    public function setUser(User $user): WishlistedGame
    {
        $this->user = $user;
        return $this;
    }

}
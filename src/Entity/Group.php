<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="App\Repository\GroupRepository")
 * @ORM\Table("groups",
 *     uniqueConstraints={@ORM\UniqueConstraint(columns={"steam_id"})}
 * )
 */
class Group
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"export"})
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="steam_id", type="string", length=18, nullable=false)
     * @Groups({"export"})
     */
    private $steamId;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Groups({"export"})
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Groups({"export"})
     */
    private $url;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"export"})
     */
    private $avatar;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"export"})
     */
    private $sgLink;

    /**
     * @var User[]
     * @ORM\ManyToMany(targetEntity="User", inversedBy="groups", cascade={"persist"})
     * @ORM\JoinTable(name="user_group",
     *     joinColumns=         {@ORM\JoinColumn(name="group_id")},
     *     inverseJoinColumns=  {@ORM\JoinColumn(name="user_id")}
     * )
     * @Groups({"export-members"})
     * @ORM\OrderBy({"activeInGroups" = "DESC", "profileName" = "ASC"})
     */
    private $members;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSteamId(): string
    {
        return $this->steamId;
    }

    /**
     * @param string $steamId
     * @return Group
     */
    public function setSteamId(string $steamId): Group
    {
        $this->steamId = $steamId;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Group
     */
    public function setName(string $name): Group
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return Group
     */
    public function setUrl(string $url): Group
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getAvatar(): string
    {
        return $this->avatar;
    }

    /**
     * @param string $avatar
     * @return Group
     */
    public function setAvatar(string $avatar): Group
    {
        $this->avatar = $avatar;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getSgLink(): ?string
    {
        return $this->sgLink;
    }

    /**
     * @param string $sgLink
     * @return Group
     */
    public function setSgLink(string $sgLink): Group
    {
        $this->sgLink = $sgLink;
        return $this;
    }

    /**
     * @return User[]|ArrayCollection|null
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * @param User[] $members
     * @return Group
     */
    public function setMembers(iterable $members): Group
    {
        $this->members->clear();
        foreach ($members as $member) {
            $this->addMember($member);
        }

        return $this;
    }

    public function addMember(User $member)
    {
        if ($this->members->contains($member))
            return $this;

        $this->members[] = $member;
        return $this;
    }

    /**
     * @return int
     * @Groups({"export"})
     */
    public function getMemberCount()
    {
        return $this->members->count();
    }

    public function __construct()
    {
        $this->members = new ArrayCollection();
    }
}
<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Knojector\SteamAuthenticationBundle\User\SteamUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use Symfony\Component\Security\Core\Role\Role as SecurityRole;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * We have to copy AbstractStemUser stuff because it conflicts with RBAC bundle
 * Also we can't use Groups annotation without copying properties
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table("users",
 *     uniqueConstraints={@ORM\UniqueConstraint(columns={"steam_id"})}
 * )
 */
class User implements SteamUserInterface, UserInterface
{
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_GIFTER = 'ROLE_GIFTER';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"export", "shortlist"})
     */
    private $id;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $enabled = true;

    /**
     * @var string
     * @ORM\Column(name="steam_id", type="string", length=17)
     * //@Groups({"export"})
     */
    private $steamId;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $communityVisibilityState;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $profileState;

    /**
     * @var string
     * @ORM\Column(type="string", length=64)
     * @Groups({"export", "shortlist"})
     */
    private $profileName;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastLogOff;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $commentPermission;

    /**
     * @var string
     * @ORM\Column(type="string", length=160)
     * @Groups({"export"})
     */
    private $profileUrl;

    /**
     * @var string
     * @ORM\Column(type="string", length=160)
     * @Groups({"export"})
     */
    private $avatar;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $personaState;

    /**
     * @var int|null
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $primaryClanId;

    /**
     * @var DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $joinDate;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true, length=4)
     */
    private $countryCode;

    /**
     * @var string[]
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var Group[]
     * @ORM\ManyToMany(targetEntity="Group", mappedBy="members", cascade={"persist"})
     * @Groups({"export-groups"})
     */
    private $groups;

    /**
     * @var int|null
     * @Groups({"export-count"})
     */
    private $ownedCount;

    /**
     * @var int|null
     * @Groups({"export-count"})
     */
    private $wishlistedCount;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": true})
     * @Groups({"export"})
     */
    private $activeInGroups = true;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true, length=64)
     * @Groups({"export"})
     */
    private $customName;

    /**
     * @var DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"export"})
     */
    private $lastUpdated;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": false}, nullable=true)
     * @Groups({"export"})
     */
    private $isSkipped;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return User
     */
    public function setEnabled(bool $enabled): User
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return int
     */
    public function getSteamId(): int
    {
        return $this->steamId;
    }

    /**
     * @param int $steamId
     * @return User
     */
    public function setSteamId(int $steamId): User
    {
        $this->steamId = $steamId;
        return $this;
    }

    /**
     * @return int
     */
    public function getCommunityVisibilityState(): int
    {
        return $this->communityVisibilityState;
    }

    /**
     * @param int|null $communityVisibilityState
     * @return User
     */
    public function setCommunityVisibilityState(?int $communityVisibilityState): User
    {
        $this->communityVisibilityState = (int)$communityVisibilityState;
        return $this;
    }

    /**
     * @return int
     */
    public function getProfileState(): int
    {
        return $this->profileState;
    }

    /**
     * @param int|null $profileState
     * @return User
     */
    public function setProfileState(?int $profileState): User
    {
        $this->profileState = (int)$profileState;
        return $this;
    }

    /**
     * @return string
     */
    public function getProfileName(): string
    {
        return $this->profileName;
    }

    /**
     * @param string $profileName
     * @return User
     */
    public function setProfileName(string $profileName): User
    {
        $this->profileName = $profileName;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getLastLogOff(): DateTime
    {
        return $this->lastLogOff;
    }

    /**
     * @param int|DateTime $lastLogOff
     * @return User
     * @throws Exception
     */
    public function setLastLogOff(/** @noinspection PhpHierarchyChecksInspection PhpSignatureMismatchDuringInheritanceInspection */
        $lastLogOff): User
    {
        if ($lastLogOff instanceof DateTime)
        {
            $this->lastLogOff = $lastLogOff;
            return $this;
        }

        if ($lastLogOff === null)
        {
            $this->lastLogOff = null;
            return $this;
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $lastLogOffDate = new DateTime();
        $lastLogOffDate->setTimestamp($lastLogOff);
        $this->lastLogOff = $lastLogOffDate;
        return $this;
    }

    /**
     * @return int
     */
    public function getCommentPermission(): int
    {
        return $this->commentPermission;
    }

    /**
     * @param int|null $commentPermission
     * @return User
     */
    public function setCommentPermission(?int $commentPermission): User
    {
        $this->commentPermission = (int)$commentPermission;
        return $this;
    }

    /**
     * @return string
     */
    public function getProfileUrl(): string
    {
        return $this->profileUrl;
    }

    /**
     * @param string $profileUrl
     * @return User
     */
    public function setProfileUrl(string $profileUrl): User
    {
        $this->profileUrl = $profileUrl;
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
     * @return User
     */
    public function setAvatar(string $avatar): User
    {
        $this->avatar = $avatar;
        return $this;
    }

    /**
     * @return int
     */
    public function getPersonaState(): int
    {
        return $this->personaState;
    }

    /**
     * @param int|null $personaState
     * @return User
     */
    public function setPersonaState(?int $personaState): User
    {
        $this->personaState = (int)$personaState;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPrimaryClanId(): ?int
    {
        return $this->primaryClanId;
    }

    /**
     * @param int|null $primaryClanId
     * @return User
     */
    public function setPrimaryClanId(?int $primaryClanId): User
    {
        $this->primaryClanId = $primaryClanId;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getJoinDate(): ?DateTime
    {
        return $this->joinDate;
    }

    /**
     * @param int|null $joinDate
     * @return User
     */
    public function setJoinDate(?int $joinDate): User
    {
        if ($joinDate !== null) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $joinDateDate = new DateTime();
            $joinDateDate->setTimestamp($joinDate);
            $joinDate = $joinDateDate;
        }

        $this->joinDate = $joinDate;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    /**
     * @param string|null $countryCode
     * @return User
     */
    public function setCountryCode(?string $countryCode): User
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = [];

        foreach ($roles as $role) {
            $this->addRole($role, false);
        }

        return $this;
    }

    public function addRole(string $role, $checkExists = true): self
    {
        if ($this->hasRole($role)) {
            return $this;
        }

        $this->roles[] = $role;
        return $this;
    }

    public function removeRole(string $role): self
    {
        $this->roles = array_values(array_diff($this->roles, [$role]));

        return $this;
    }

    /**
     * @return SecurityRole[]
     */
    public function getRoles(): array
    {
        $roles = array_map(function (string $role) {
            return new SecurityRole($role);
        }, $this->roles);

        return $roles;
    }

    /**
     * @return bool
     * @Groups({"export", "export-roles"})
     */
    public function isAdmin()
    {
        return $this->hasRole('ROLE_ADMIN');
    }

    /**
     * @return bool
     * @Groups({"export", "export-roles"})
     */
    public function isGifter()
    {
        return $this->hasRole('ROLE_GIFTER');
    }

    /**
     * @param array $userData
     */
    public function update(array $userData)
    {
        $this->setCommunityVisibilityState($userData['communityvisibilitystate']);
        $this->setProfileState($userData['profilestate']);
        $this->setProfileName($userData['personaname']);
        $this->setLastLogOff($userData['lastlogoff']);
        $this->setCommentPermission($userData['commentpermission']);
        $this->setProfileUrl($userData['profileurl']);
        $this->setAvatar($userData['avatarfull']);
        $this->setPersonaState($userData['personastate']);
        $this->setPrimaryClanId(
            isset($userData['primaryclanid']) ? $userData['primaryclanid'] : null
        );
        $this->setCountryCode(
            isset($userData['loccountrycode']) ? $userData['loccountrycode'] : null
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->steamId;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        return;
    }

    /**
     * @Groups({"export", "shortlist"})
     */
    public function getSteamId64()
    {
        return (string)$this->getSteamId();
    }

    public function setPersonaName(string $personaName)
    {
        $this->setProfileName($personaName);
        return $this;
    }

    /**
     * @param $timeCreated
     * @return $this
     * @throws Exception
     */
    public function setTimeCreated($timeCreated)
    {
        if ($timeCreated === null)
        {
            $this->setJoinDate($timeCreated);
            return $this;
        }

        try {
            $dateTime = new DateTime($timeCreated);
        } catch (Exception $e) {
            throw new Exception("User: Can't parse DateTime from timeCreated string: ".$timeCreated);
        }

        $this->setJoinDate( $dateTime->getTimestamp() );
        return $this;
    }

    public function setLocCountryCode(?string $locCountryCode)
    {
        $this->setCountryCode($locCountryCode);
        return $this;
    }

    /**
     * @return Group[]|ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param Group[] $groups
     * @return User
     */
    public function setGroups(array $groups): User
    {
        $this->groups->clear();
        foreach ($groups as $group) {
            $this->addGroup($group);
        }

        return $this;
    }

    /**
     * @param Group $group
     * @return $this
     */
    public function addGroup(Group $group)
    {
        if ($this->groups->contains($group)) {
            return $this;
        }

        $group->addMember($this);
        $this->groups[] = $group;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getOwnedCount(): ?int
    {
        return $this->ownedCount;
    }

    /**
     * @param int|null $ownedCount
     * @return User
     */
    public function setOwnedCount(?int $ownedCount): User
    {
        $this->ownedCount = $ownedCount;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getWishlistedCount(): ?int
    {
        return $this->wishlistedCount;
    }

    /**
     * @param int|null $wishlistedCount
     * @return User
     */
    public function setWishlistedCount(?int $wishlistedCount): User
    {
        $this->wishlistedCount = $wishlistedCount;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActiveInGroups(): bool
    {
        return $this->activeInGroups;
    }

    /**
     * @param bool $activeInGroups
     * @return User
     */
    public function setActiveInGroups(bool $activeInGroups): User
    {
        $this->activeInGroups = $activeInGroups;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCustomName(): ?string
    {
        return $this->customName;
    }

    /**
     * @param string $customName
     * @return User
     */
    public function setCustomName(string $customName): User
    {
        $this->customName = $customName;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getLastUpdated(): ?DateTime
    {
        return $this->lastUpdated;
    }

    /**
     * @param DateTime|null $lastUpdated
     * @return User
     */
    public function setLastUpdated(?DateTime $lastUpdated): User
    {
        $this->lastUpdated = $lastUpdated;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getisSkipped(): ?bool
    {
        return $this->isSkipped;
    }

    /**
     * @param bool $isSkipped
     * @return User
     */
    public function setIsSkipped(bool $isSkipped): User
    {
        $this->isSkipped = $isSkipped;

        return $this;
    }

    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

}
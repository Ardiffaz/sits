<?php

namespace App\Tools\SteamApi\Object;

class Player extends BaseObject
{
    public $steamId64;
    public $communityVisibilityState;
    public $profileState;
    public $personaName;
    public $lastLogOff;
    public $commentPermission;
    public $profileUrl;
    public $avatar;
    public $avatarMedium;
    public $avatarFull;
    public $personaState;
    public $primaryClanId;
    public $timeCreated;
    public $personaStateFlags;
    public $realName;
    public $locCountryCode;

    public function setSteamId($steamId64)
    {
        $this->setSteamId64($steamId64);
        return $this;
    }

    public function getSteamId()
    {
        return $this->steamId64;
    }

    /**
     * @param string|int $steamId64
     * @return Player
     */
    public function setSteamId64($steamId64)
    {
        $this->steamId64 = (string)$steamId64;
        return $this;
    }

    /**
     * @param int $communityVisibilityState
     * @return Player
     */
    public function setCommunityVisibilityState(int $communityVisibilityState)
    {
        $this->communityVisibilityState = $communityVisibilityState;
        return $this;
    }

    /**
     * @param int|null $profileState
     * @return Player
     */
    public function setProfileState(?int $profileState)
    {
        $this->profileState = $profileState;
        return $this;
    }

    /**
     * @param string $personaName
     * @return Player
     */
    public function setPersonaName(string $personaName)
    {
        $this->personaName = $personaName;
        return $this;
    }

    /**
     * @param int|null $lastLogOff
     * @return Player
     */
    public function setLastLogOff(?int $lastLogOff)
    {
        $this->lastLogOff = $this->convertDate($lastLogOff);
        return $this;
    }

    /**
     * @param int|null $commentPermission
     * @return Player
     */
    public function setCommentPermission(?int $commentPermission)
    {
        $this->commentPermission = $commentPermission;
        return $this;
    }

    /**
     * @param string $profileUrl
     * @return Player
     */
    public function setProfileUrl(string $profileUrl)
    {
        $this->profileUrl = $profileUrl;
        return $this;
    }

    /**
     * @param string $avatar
     * @return Player
     */
    public function setAvatar(string $avatar)
    {
        $this->avatar = $avatar;
        return $this;
    }

    /**
     * @param string $avatarMedium
     * @return Player
     */
    public function setAvatarMedium(string $avatarMedium)
    {
        $this->avatarMedium = $avatarMedium;
        return $this;
    }

    /**
     * @param string $avatarFull
     * @return Player
     */
    public function setAvatarFull(string $avatarFull)
    {
        $this->avatarFull = $avatarFull;
        return $this;
    }

    /**
     * @param int $personaState
     * @return Player
     */
    public function setPersonaState(int $personaState)
    {
        $this->personaState = $personaState;
        return $this;
    }

    /**
     * @param string|null $primaryClanId
     * @return Player
     */
    public function setPrimaryClanId(?string $primaryClanId)
    {
        $this->primaryClanId = $primaryClanId;
        return $this;
    }

    /**
     * @param int|null $timeCreated
     * @return Player
     */
    public function setTimeCreated(?int $timeCreated)
    {
        $this->timeCreated = $this->convertDate($timeCreated);
        return $this;
    }

    /**
     * @param int|null $personaStateFlags
     * @return Player
     */
    public function setPersonaStateFlags(?int $personaStateFlags)
    {
        $this->personaStateFlags = $personaStateFlags;
        return $this;
    }

    /**
     * @param string|null $realName
     * @return Player
     */
    public function setRealName(?string $realName)
    {
        $this->realName = $realName;
        return $this;
    }

    /**
     * @param string|null $locCountryCode
     * @return Player
     */
    public function setLocCountryCode(?string $locCountryCode)
    {
        $this->locCountryCode = $locCountryCode;
        return $this;
    }
}
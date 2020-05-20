<?php

namespace App\Tools\SteamApi\Object;

use Symfony\Component\Serializer\Annotation\Groups;

class BaseGroup extends BaseObject
{

    /**
     * @Groups({"export"})
     */
    public $groupId64;

    /**
     * @Groups({"export"})
     */
    public $groupName;

    /**
     * @Groups({"export"})
     */
    public $groupUrl;

    /**
     * @Groups({"export"})
     */
    public $avatarMedium;

    /**
     * @Groups({"export"})
     */
    public $memberCount;

    /**
     * @Groups({"export"})
     */
    public function getGroupId()
    {
        return $this->groupId64;
    }

    /**
     * @param string|int $groupId64
     * @return BaseGroup
     */
    public function setGroupId64($groupId64)
    {
        $this->groupId64 = $groupId64;
        return $this;
    }

    /**
     * @param string $groupName
     * @return BaseGroup
     */
    public function setGroupName(string $groupName)
    {
        $this->groupName = $groupName;
        return $this;
    }

    /**
     * @param string $groupUrl
     * @return BaseGroup
     */
    public function setGroupUrl(string $groupUrl): BaseGroup
    {

        if (strpos($groupUrl, 'steamcommunity.com/groups/') === false)
            $groupUrl = 'https://steamcommunity.com/groups/'.$groupUrl;

        $this->groupUrl = $groupUrl;
        return $this;
    }

    /**
     * @param string $avatarMedium
     * @return BaseGroup
     */
    public function setAvatarMedium(string $avatarMedium)
    {
        $this->avatarMedium = $avatarMedium;
        return $this;
    }

    /**
     * @param int|string $memberCount
     * @return BaseGroup
     */
    public function setMemberCount($memberCount): BaseGroup
    {
        $memberCount = str_replace(',', '', $memberCount);

        $this->memberCount = intval($memberCount);
        return $this;
    }

}
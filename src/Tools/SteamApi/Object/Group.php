<?php

namespace App\Tools\SteamApi\Object;

use Symfony\Component\Serializer\Annotation\Groups;

class Group extends BaseGroup
{

    /**
     * @Groups({"export"})
     */
    public $headLine;

    /**
     * @Groups({"export"})
     */
    public $summary;

    /**
     * @Groups({"export"})
     */
    public $avatarIcon;

    /**
     * @Groups({"export"})
     */
    public $avatarFull;

    /**
     * @Groups({"export"})
     */
    public $members;

    /**
     * @param string $headLine
     * @return Group
     */
    public function setHeadLine(string $headLine)
    {
        $this->headLine = $headLine;
        return $this;
    }

    /**
     * @param string $summary
     * @return Group
     */
    public function setSummary(string $summary)
    {
        $this->summary = $summary;
        return $this;
    }

    /**
     * @param string $avatarIcon
     * @return Group
     */
    public function setAvatarIcon(string $avatarIcon)
    {
        $this->avatarIcon = $avatarIcon;
        return $this;
    }

    /**
     * @param string $avatarFull
     * @return Group
     */
    public function setAvatarFull(string $avatarFull)
    {
        $this->avatarFull = $avatarFull;
        return $this;
    }

    /**
     * @param array $members
     * @return Group
     */
    public function setMembers(array $members)
    {
        if (is_array($members['steamID64']))
            $members = $members['steamID64'];

        $this->members = $members;
        return $this;
    }

}
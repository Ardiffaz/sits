<?php

namespace App\Tools\SteamSpy\Object;

class AppDetails
{
    const HIDDEN_ID = 999999;

    /**
     * @var int Steam Application ID. If it's 999999, then data for this application is hidden on developer's request
     */
    public $id = self::HIDDEN_ID;

    /**
     * @var string|null
     */
    public $name = '';

    public function setAppid($appId)
    {
        $this->id = $appId;
        return $this;
    }

    public function setName(?string $name)
    {
        $this->name = $name ? $name : '';
        return $this;
    }

    public $developer;
    public $publisher;
    public $scoreRank;
    public $positive;
    public $negative;
    public $userscore;
    public $owners;
    public $averageforever;
    public $average2Weeks;
    public $medianForever;
    public $median2Weeks;
    public $price;
    public $initialprice;
    public $discount;
    public $languages;
    public $genre;
    public $ccu;
    public $tags;

}
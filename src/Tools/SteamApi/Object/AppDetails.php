<?php

namespace App\Tools\SteamApi\Object;

use App\Enum\AppType;
use DateTime;

class AppDetails extends BaseObject
{
    /** @var string game/dlc/demo/advertising/mod/video */
    public $type;

    /** @var string */
    public $name;

    public $appid;

    public $requiredAge;

    public $isFree;

    public $controllerSupport;

    /** @var array of dlc appids */
    public $dlc;

    public $detailedDescription;

    public $aboutTheGame;

    public $shortDescription;

    /** @var mixed Shown for movies or demos. */
    public $fullgame;

    public $supportedLanguages;

    public $headerImage;

    public $website;

    public $pcRequirements;

    public $macRequirements;

    public $linuxRequirements;

    public $legalNotice;

    public $developers;

    public $publishers;

    public $demos;

    public $priceRub;

    public $packages;

    public $packageGroups;

    public $platforms;

    public $metacritic;

    public $categories;

    public $genres;

    public $screenshots;

    public $movies;

    public $recommendations;

    public $achievements;

    public $releaseDate;

    public $supportInfo;

    public $background;

    public $contentDescriptors;


    public function setSteamAppId($steamAppId)
    {
        $this->appid = $steamAppId;
        return $this;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type)
    {
        switch ($type)
        {
            case 'game':
                $this->type = AppType::GAME;
                break;
            case 'dlc':
                $this->type = AppType::DLC;
                break;
            case 'video':
            case 'movie':
                $this->type = AppType::VIDEO;
                break;
            case 'mod':
                $this->type = AppType::MOD;
                break;
            default:
                $this->type = AppType::OTHER;
        }

        return $this;
    }

    /**
     * @param mixed $isFree
     * @return AppDetails
     */
    public function setIsFree($isFree)
    {
        $this->isFree = $isFree;

        return $this;
    }

    /**
     * @param mixed $priceOverview
     * @return AppDetails
     */
    public function setPriceOverview($priceOverview)
    {
        $priceRub = 0;

        if ($priceOverview['final'] === 'No price found')
            $priceRub = null;

        if ($priceOverview['currency'] === 'RUB')
            $priceRub = $priceOverview['initial'] / 100;

        $this->priceRub = $priceRub;
        return $this;
    }

    /**
     * @param mixed $recommendations
     * @return AppDetails
     */
    public function setRecommendations($recommendations)
    {
        $this->recommendations = $recommendations['total'];
        return $this;
    }

    /**
     * @param mixed $achievements
     * @return AppDetails
     */
    public function setAchievements($achievements)
    {
        $this->achievements = $achievements['total'];
        return $this;
    }

    /**
     * @param mixed $releaseDate
     * @return AppDetails
     */
    public function setReleaseDate($releaseDate)
    {
        if ($releaseDate['coming_soon'] === true)
            $releaseDate = null;
        else
        {
            $releaseDate = DateTime::createFromFormat('d M, Y', $releaseDate['date']);
            if (!$releaseDate)
                $releaseDate = null;
        }

        $this->releaseDate = $releaseDate;
        return $this;
    }

    /**
     * @param mixed $requiredAge
     * @return AppDetails
     */
    public function setRequiredAge($requiredAge)
    {
        $this->requiredAge = $requiredAge;
        return $this;
    }

    /**
     * @param mixed $controllerSupport
     * @return AppDetails
     */
    public function setControllerSupport($controllerSupport)
    {
        $this->controllerSupport = $controllerSupport;
        return $this;
    }

    /**
     * @param mixed $detailedDescription
     * @return AppDetails
     */
    public function setDetailedDescription($detailedDescription)
    {
        $this->detailedDescription = $detailedDescription;
        return $this;
    }

    /**
     * @param mixed $aboutTheGame
     * @return AppDetails
     */
    public function setAboutTheGame($aboutTheGame)
    {
        $this->aboutTheGame = $aboutTheGame;
        return $this;
    }

    /**
     * @param mixed $shortDescription
     * @return AppDetails
     */
    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;
        return $this;
    }

    /**
     * @param mixed $supportedLanguages
     * @return AppDetails
     */
    public function setSupportedLanguages($supportedLanguages)
    {
        $this->supportedLanguages = $supportedLanguages;
        return $this;
    }

    /**
     * @param mixed $headerImage
     * @return AppDetails
     */
    public function setHeaderImage($headerImage)
    {
        $this->headerImage = $headerImage;
        return $this;
    }

    /**
     * @param mixed $pcRequirements
     * @return AppDetails
     */
    public function setPcRequirements($pcRequirements)
    {
        $this->pcRequirements = $pcRequirements;
        return $this;
    }

    /**
     * @param mixed $macRequirements
     * @return AppDetails
     */
    public function setMacRequirements($macRequirements)
    {
        $this->macRequirements = $macRequirements;
        return $this;
    }

    /**
     * @param mixed $linuxRequirements
     * @return AppDetails
     */
    public function setLinuxRequirements($linuxRequirements)
    {
        $this->linuxRequirements = $linuxRequirements;
        return $this;
    }

    /**
     * @param mixed $legalNotice
     * @return AppDetails
     */
    public function setLegalNotice($legalNotice)
    {
        $this->legalNotice = $legalNotice;
        return $this;
    }

    /**
     * @param mixed $packageGroups
     * @return AppDetails
     */
    public function setPackageGroups($packageGroups)
    {
        $this->packageGroups = $packageGroups;
        return $this;
    }

    /**
     * @param mixed $contentDescriptors
     * @return AppDetails
     */
    public function setContentDescriptors($contentDescriptors)
    {
        $this->contentDescriptors = $contentDescriptors;
        return $this;
    }





}
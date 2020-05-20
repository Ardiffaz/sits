<?php

namespace App\Tools\SteamApi\Object;

class AppBasic extends BaseObject
{

    public $appId;

    public $name;

    /**
     * @param mixed $appId
     * @return AppBasic
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
        return $this;
    }

    /**
     * @param mixed $name
     * @return AppBasic
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }


}
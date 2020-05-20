<?php

namespace App\Tools\SteamApi\Object;

use DateTime;
use Exception;

class BaseObject
{
    protected function convertPersonaStateToText($personaStateId)
    {
        switch ($personaStateId)
        {
            case 0:
                return 'Offline';
            case 1:
                return 'Online';
            case 2:
                return 'Busy';
            case 3:
                return 'Away';
            case 4:
                return 'Snooze';
            case 5:
                return 'Looking to Trade';
            case 6:
                return 'Looking to Play';
            default:
                return null;
        }
    }

    /**
     * @param $timestamp
     * @return DateTime
     * @throws Exception
     */
    protected function convertDate($timestamp)
    {
        if (!$timestamp)
            return null;

        /** @noinspection PhpUnhandledExceptionInspection */
        $dateTime = new DateTime();

        $dateTime->setTimestamp($timestamp);
        return $dateTime;
    }
}
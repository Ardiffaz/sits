<?php

namespace App\Tools\SteamApi;

use App\Tools\SteamApi\Object\AppBasic;
use App\Tools\SteamApi\Object\AppDetails;
use App\Tools\SteamApi\Object\AppReviews;
use App\Tools\SteamApi\Object\BaseGroup;
use App\Tools\SteamApi\Object\Group;
use App\Tools\SteamApi\Object\Player;
use Exception;
use Symfony\Component\DomCrawler\Crawler;

class ApiClient extends BaseApiClient
{
    /**
     * @param array|string $steamIds
     * @return Player[]|object|null
     * @throws Exception
     */
    public function getPlayerSummaries($steamIds)
    {
        $urlService = 'ISteamUser/GetPlayerSummaries/v0002/';

        $steamIdsChunks = [];

        if (!is_array($steamIds))
            $steamIdsChunks[] = $steamIds;
        else
        {
            // "GetPlayerSummaries" has limitation of max 100 steamIds
            $chunks = array_chunk($steamIds, 99);

            foreach ($chunks as $chunk)
                $steamIdsChunks[] = implode(',', $chunk);
        }

        $players = [];

        foreach ($steamIdsChunks as $steamIdsChunk) {
            $response = $this->getApiJsonResponse($urlService, ['steamids' => $steamIdsChunk]);

            if (!$response->response->players)
                continue;

            $playersChunk = $this->serializer->deserialize(
                json_encode($response->response->players),
                Player::class.'[]',
                'json'
            );

            $players = array_merge($players, $playersChunk);
        }

        return count($players) ? $players : null;
    }

    /**
     * Parses XML list of user groups, but only first 3 groups have some data except ids,
     * so only ids are used here.
     *
     * @param $userSteamId
     * @return array
     * @throws Exception
     */
    public function getUserGroupIdsList($userSteamId)
    {
        $url = self::BASE_PROFILE_URL.$userSteamId.'/?xml=1';

        $crawler = $this->getXmlResponse($url);

        $groupIds = [];
        foreach ($crawler->filterXPath('profile/groups/group') as $groupDOM)
        {
            $groupId = $groupDOM->getElementsByTagName('groupID64')[0]->textContent;

            $groupIds[] = $groupId;
        }

        return $groupIds;
    }

    /**
     * Parses HTML list of user groups.
     *
     * @param $userSteamId
     * @return BaseGroup[]
     * @throws Exception
     */
    public function getUserGroupList($userSteamId)
    {
        $url = self::BASE_PROFILE_URL.$userSteamId.'/groups/';

        $crawler = $this->getHtmlResponse($url);

        $groups = [];
        foreach ($crawler->filter('.profile_groups .group_block') as $groupDom) {
            $groupCrawler = new Crawler($groupDom);

            $group = new BaseGroup();

            $titleObj = $groupCrawler->filter('.linkTitle');
            if ($titleObj)
            {
                $group->setGroupName( $titleObj->text() )
                    ->setGroupUrl( $titleObj->attr('href') );
            }

            $avatarObj = $groupCrawler->filter('.avatarMedium img');
            if ($avatarObj)
               $group->setAvatarMedium( $avatarObj->attr('src') );

            $memberCountObj = $groupCrawler->filter('.memberRow > .groupMemberStat');
            if ($memberCountObj)
                $group->setMemberCount( $memberCountObj->text() );

            $groupIdObj = $groupCrawler->filter('.memberRow .steamLink:last-child');
            if ($groupIdObj)
            {
                $m = [];
                preg_match('~\d{18}~', $groupIdObj->attr('href'), $m);

                $group->setGroupId64( $m[0] );
            }

            $groups[] = $group;
        }

        return $groups;
    }

    /**
     * @param $groupId64
     * @return Group
     * @throws Exception
     */
    public function getGroupSummary($groupId64)
    {
        $url = self::BASE_COMMUNITY_URL.'gid/'.$groupId64.'/memberslistxml?xml=1';

        $crawler = $this->getXmlResponse($url)->filter('memberList');

        $groupName = $crawler->filter('groupName')->text();

        if (!$groupName)
            throw new Exception('Group #'.$groupId64.' not found.');

        $groupXml = '<group>'.$this->getXml($crawler).'</group>';
        $group = $this->serializer->deserialize( $groupXml,Group::class,'xml' );

        $groupDetailsXml = '<group>'.$this->getXml( $crawler->filter('groupDetails') ).'</group>';

        /** @var Group $groupData */
        $groupData = $this->serializer->deserialize( $groupDetailsXml, Group::class, 'xml', ['object_to_populate' => $group] );

        return $groupData;
    }


    /**
     * @return AppBasic[]|null
     * @throws Exception
     */
    public function getAppList()
    {
        $urlService = 'ISteamApps/GetAppList/v2';
        $url = self::BASE_API_URL.$urlService;

        $response = $this->getJsonResponse($url);

        if (!$response->applist->apps)
            return null;

        /** @var AppBasic[] $apps */
        $apps = $this->serializer->deserialize(
            json_encode($response->applist->apps),
            AppBasic::class.'[]',
            'json'
        );

        return $apps;
    }

    /**
     * @return AppBasic[]|object|null
     * @throws Exception
     */
    public function getAppListV2()
    {
        $urlService = 'IStoreService/GetAppList/v1';

        $baseParams = [
            'include_games' => 1,
            'include_dlc' => 1,
            'include_software' => 1,
            'include_videos' => 1,
            'include_hardware' => 1,
            'max_results' => 40000
        ];

        $lastAppId = 0;

        $apps = [];

        do {

            $params = $baseParams;
            if ($lastAppId)
                $params['last_appid'] = $lastAppId;

            $response = $this->getApiJsonResponse($urlService, $params);

            $lastAppId = isset($response->response->last_appid) ? $response->response->last_appid : 0;

            if (!$response->response->apps)
                break;

            $apps = array_merge(
                $apps,
                $this->serializer->deserialize(
                    json_encode($response->response->apps),
                    AppBasic::class.'[]',
                    'json'
                )
            );

        } while ($lastAppId);

        return $apps;
    }

    /**
     * @param $profileUrl
     * @param bool $alsoGetWishlisted
     * @return array
     * @throws Exception
     */
    public function getUserGames($profileUrl, $alsoGetWishlisted = false)
    {
        sleep(2);
        set_time_limit(1800);
        $url = $profileUrl.'/games/?tab=all';
        $html = $this->getHtmlResponse($url)->text();

        $result = [];
        $result['owned'] = $this->getAppIdsByNeedle(self::GAMES_NEEDLE, $html);

        if ($alsoGetWishlisted)
            $result['wishlisted'] = $this->getAppIdsByNeedle(self::WISHLISTED_GAMES_NEEDLE, $html);

        return $result;
    }


    /**
     * @param $userSteamId
     * @return array
     * @throws Exception
     */
    public function getOwnedGames($userSteamId)
    {
        $result = $this->getUserGames($userSteamId);
        return $result['owned'];
    }

    /**
     * @param $userSteamId
     * @return array
     * @throws Exception
     */
    public function getUserWishlist($userSteamId)
    {
        sleep(2);
        $url = self::BASE_PROFILE_URL.$userSteamId.'/wishlist/';
        $html = $this->getHtmlResponse($url)->html();

        $wishlistedIds = $this->getAppIdsByNeedle(self::WISHLISTED_GAMES_NEEDLE, $html);

        return $wishlistedIds;
    }

    /**
     * @param $userSteamId
     * @return array|null
     * @throws Exception
     */
    public function getOwnedGamesApi($userSteamId)
    {
        $urlService = 'IPlayerService/GetOwnedGames/v1';
        $params = ['steamid' => $userSteamId, 'include_played_free_games' => 1];

        $response = $this->getApiJsonResponse($urlService, $params);

        if (!$response->response || !isset($response->response->games) || !$response->response->games)
            return null;

        $gameIds = array_map(function($item){
            return $item->appid;
        }, $response->response->games);

        return $gameIds;
    }

    /**
     * @param $gameId
     * @return mixed
     * @throws Exception
     */
    public function getAppDetails($gameId)
    {
        $url = self::BASE_APPDETAILS_URL.'?'.urldecode(http_build_query(['appids' => $gameId, 'cc' => 'ru']));
        $response = $this->getJsonResponse($url);

        if ($response->{$gameId}->success === false)
            return null;

        /** @var AppDetails $game */
        $game = $apps = $this->serializer->deserialize(
            json_encode($response->{$gameId}->data),
            AppDetails::class,
            'json'
        );

        return $game;
    }

    /**
     * @param $gameId
     * @return AppReviews
     * @throws Exception
     */
    public function getAppReviews($gameId)
    {
        $url = self::BASE_APPREVIEWS_URL.$gameId.'?'.urldecode(http_build_query([
                'filter' => 'summary',
                'language' => 'all',
                'review_type' => 'all',
                'purchase_type' => 'steam'
            ]));

        $appReviews = new AppReviews();

        $response = $this->getJsonResponse($url);

        if ($response->success === 2)
        {
            $appReviews->rating = null;
            $appReviews->reviewCount = null;

            return $appReviews;
        }

        $reviewScore = $response->review_score;

        if (preg_match('#(\d+)% of the (\d+(,\d+)?(,\d+)?)#', $reviewScore, $m))
        {
            list(, $percent, $count) = $m;

            $count = str_replace(',', '', $count);

            $appReviews->reviewCount = $count;
            $appReviews->rating = $percent;
        }
        elseif(preg_match("#<b>(\d+)</b> reviews#", $reviewScore, $m))
        {
            $count = str_replace(',', '', $m[1]);

            $appReviews->reviewCount = $count;
            $appReviews->rating = 0;
        }

        return $appReviews;

    }
}
<?php

namespace App\Operation\User;

use App\Entity\User;
use App\Operation\BaseOperation;
use App\Tools\SteamApi\ApiClient;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Serializer\SerializerInterface;

class SavingUserFromSteam extends BaseOperation
{
    private $userSteamIds;

    /** @var ApiClient */
    private $apiClient;

    public function __construct(ApiClient $apiClient, SerializerInterface $serializer, EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, $serializer);
        $this->apiClient = $apiClient;
    }

    public function setUserId($userSteamIds)
    {
        $this->userSteamIds = is_array($userSteamIds) ? $userSteamIds : [$userSteamIds];
        return $this;
    }

    /**
     * @return User[]
     * @throws Exception
     */
    public function execute()
    {
        if (!$this->userSteamIds)
            throw new Exception(self::class.': UserSteamIds is not set');

        $users = [];

        $userRepo = $this->em->getRepository(User::class);
        /** @var User[] $existedUsers */
        $existedUsers = $userRepo->findBy(['steamId' => $this->userSteamIds]);

        $existedUsersBySteamId = [];
        foreach ($existedUsers as $existedUser) {
            $existedUsersBySteamId[ $existedUser->getSteamId64() ] = $existedUser;
        }

        $steamUsers = $this->apiClient->getPlayerSummaries( $this->userSteamIds );

        function convertDateTime (DateTime $dateTime)
        {
            return $dateTime->getTimestamp();
        }

        foreach ($steamUsers as $steamUser)
        {

            if (isset($existedUsersBySteamId[$steamUser->steamId64]))
            {
                $user = $existedUsersBySteamId[$steamUser->steamId64];
            }
            else
            {
                $user = new User();
                $user->setSteamId( $steamUser->steamId64 );
            }

            $joinDate = $steamUser->timeCreated ? convertDateTime($steamUser->timeCreated) : null;
            $lastLogOff = $steamUser->lastLogOff ? convertDateTime($steamUser->lastLogOff) : null;

            $user
                ->setCommunityVisibilityState( $steamUser->communityVisibilityState )
                ->setProfileState( $steamUser->profileState )
                ->setProfileName( $steamUser->personaName )
                ->setLastLogOff( $lastLogOff )
                ->setCommentPermission( $steamUser->commentPermission )
                ->setProfileUrl( $steamUser->profileUrl )
                ->setAvatar( $steamUser->avatarMedium )
                ->setPersonaState( $steamUser->personaState )
                ->setPrimaryClanId( $steamUser->primaryClanId )
                ->setJoinDate( $joinDate )
                ->setCountryCode( $steamUser->locCountryCode );

            $users[] = $user;

            $this->em->persist($user);
        }

        $this->em->flush();

        // to get them in an appropriate order
        $users = $this->em->createQueryBuilder()
            ->from(User::class, 'u')
            ->select('u')
            ->andWhere('u.steamId in (:steamIds)')
            ->setParameter('steamIds', $this->userSteamIds)
            ->orderBy('u.activeInGroups', 'DESC')
            ->addOrderBy('u.profileName', 'ASC')
            ->getQuery()
            ->getResult();

        return $users;
    }
}
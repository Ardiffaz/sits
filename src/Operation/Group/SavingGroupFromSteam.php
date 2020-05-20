<?php

namespace App\Operation\Group;

use App\Entity\Group;
use App\Operation\BaseOperation;
use App\Operation\FetchSteam\FetchingGroup;
use App\Operation\FetchSteam\FetchingUser;
use App\Operation\Sync\SyncingUserGames;
use App\Operation\User\SavingUserFromSteam;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Serializer\SerializerInterface;

class SavingGroupFromSteam extends BaseOperation
{
    protected $fetchingSteamGroup;
    protected $fetchingUser;
    protected $savingUserFromSteam;
    protected $syncingUserGames;

    protected $groupSteamId;
    protected $groupId;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        FetchingGroup $fetchingGroup,
        FetchingUser $fetchingUser,
        SavingUserFromSteam $savingUserFromSteam,
        SyncingUserGames $syncingUserGames
    )
    {
        parent::__construct($entityManager, $serializer);
        $this->fetchingSteamGroup = $fetchingGroup;
        $this->fetchingUser = $fetchingUser;
        $this->savingUserFromSteam = $savingUserFromSteam;
        $this->syncingUserGames = $syncingUserGames;
    }

    public function setGroupSteamId($groupSteamId)
    {
        $this->groupSteamId = $groupSteamId;
        return $this;
    }

    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
        return $this;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function execute()
    {
        $groupRepo = $this->em->getRepository(Group::class);
        $existed = null;
        if ($this->groupId)
        {
            $existed = $groupRepo->find( $this->groupId );

            $this->setGroupSteamId( $existed->getSteamId() );
        }

        if (!$this->groupSteamId)
            throw new Exception(self::class.': GroupSteamId is not set.');

        if (!$existed)
        {
            $existedResult = $groupRepo->findBy(['steamId' => $this->groupSteamId]);
            /** @var Group $existed */
            $existed = reset($existedResult);
        }

        $steamGroup = $this->fetchingSteamGroup->setGroupId64($this->groupSteamId)->execute();

        if ($steamGroup->memberCount > 500)
            throw new Exception("Won't save group with 500+ members.");

        $group = $existed ? $existed : new Group();

        $group->setSteamId( $steamGroup->groupId64 )
            ->setName( $steamGroup->groupName )
            ->setUrl( $steamGroup->groupUrl )
            ->setAvatar( $steamGroup->avatarMedium );

        $this->em->persist($group);
        $this->em->flush();

        $this->setGroupId( $group->getId() );

        $members = $steamGroup->members;

        // user entities
        $users = $this->savingUserFromSteam->setUserId($members)->execute();

        $group->setMembers($users);

        $this->em->flush();

        return $group;
    }
}
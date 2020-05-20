<?php

namespace App\Operation\Group;

use App\Entity\Game;
use App\Entity\Group;
use App\Entity\OwnedGame;
use App\Entity\WishlistedGame;
use App\Operation\BaseOperation;
use App\Operation\Game\FindingGames;
use App\Operation\User\GettingUserList;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;
use Symfony\Component\Serializer\SerializerInterface;

class CheckingGames extends BaseOperation
{
    /** @var GettingUserList */
    protected $gettingUserList;

    /** @var FindingGames */
    protected $findingGames;

    /** @var Group */
    protected $group;

    /** @var string */
    protected $searchParam;

    /** @var int */
    protected $pageNumber = 1;

    /** @var int */
    protected $itemsPerPage = 15;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer, GettingUserList $gettingUserList, FindingGames $findingGames)
    {
        parent::__construct($entityManager, $serializer);
        $this->gettingUserList = $gettingUserList;
        $this->findingGames = $findingGames;
    }

    /**
     * @param int $groupId
     * @return $this
     * @throws Exception
     */
    public function setGroupId(int $groupId)
    {
        $group = $this->em->getRepository(Group::class)->find( $groupId );

        if (!$group)
            throw new Exception(self::class.': Group id#'.$groupId.' not found');

        $this->group = $group;
        return $this;
    }

    public function setGroup(Group $group)
    {
        $this->group = $group;
        return $this;
    }

    public function setSearchParam(string $searchParam)
    {
        $this->searchParam = $searchParam;
        return $this;
    }

    public function setPageNumber(int $pageNumber)
    {
        $this->pageNumber = $pageNumber > 1 ? $pageNumber : 1;
        return $this;
    }

    public function setItemsPerPage(int $itemsPerPage)
    {
        $this->itemsPerPage = $itemsPerPage;
        return $this;
    }

    /**
     * @return Game[]
     * @throws Exception
     */
    public function execute()
    {
        if (!$this->group)
            throw new Exception(self::class.': Group Id not set.');

        $members = $this->gettingUserList->setGroup($this->group)->execute();
        $games = [];
        $maxPageNumber = 1;

        // if search string is set, find games by filter
        if ($this->searchParam)
        {
            $gamesResult =
                $this->findingGames
                ->setItemsPerPage( $this->itemsPerPage )
                ->setPageNumber( $this->pageNumber )
                ->setSearchParam( $this->searchParam )
                ->execute();

            $maxPageNumber = $gamesResult['maxPageNumber'];

            /** @var Game $gameItem */
            foreach ($gamesResult['games'] as $gameItem) {

                $gameId = $gameItem->getId();
                $games[ $gameId ] = $gameItem;
            }
        }
        else
        {
            // if search param is not set, then just show top wishlisted games

            $queryBuilder = $this->em->createQueryBuilder()
                ->from(Game::class, 'g')
                ->setMaxResults(50)
                ->addOrderBy('g.id')
                ->leftJoin('g.wishlistedGames', 'wg')
                ->select('DISTINCT g as game',  'count(DISTINCT wg.user) as WLcount')
                ->groupBy('g.id')
                ->orderBy('WLcount', 'DESC')
                ->andWhere('wg.user in (:members)')
                ->setParameter('members', $members);

            $paginator = new Paginator($queryBuilder->getQuery());

            $paginator->getQuery()
                ->setFirstResult($this->itemsPerPage*($this->pageNumber - 1))
                ->setMaxResults($this->itemsPerPage);

            /** @var Game[] $games */
            $gamesResult = $paginator->getIterator();

            $totalCount = $paginator->count();
            $maxPageNumber = ceil($totalCount / $this->itemsPerPage);

            foreach ($gamesResult as ['game' => $game]) {
                $gameId = $game->getId();
                $games[ $gameId ] = $game;
            }
        }

        $wishlistResult = $this->em->createQueryBuilder()
            ->from(WishlistedGame::class, 'wg')
            ->andWhere('wg.user in (:members)')
            ->andWhere('wg.game in (:games)')
            ->setParameters([
                'members' => $members,
                'games' => $games,
            ])
            ->select('identity(wg.user) as userId', 'identity(wg.game) as gameId')
            ->getQuery()
            ->getArrayResult();

        foreach ($wishlistResult as $wishlistResultItem) {
            $userId = $wishlistResultItem['userId'];
            $gameId = $wishlistResultItem['gameId'];

            $games[ $gameId ]->addWishlistedBy($userId);
        }

        $ownedResult = $this->em->createQueryBuilder()
            ->from(OwnedGame::class, 'og')
            ->select('og')
            ->andWhere('og.user in (:members)')
            ->andWhere('og.game in (:games)')
            ->setParameters([
                'members' => $members,
                'games' => $games
            ])
            ->select('identity(og.user) as userId', 'identity(og.game) as gameId', 'og.active as active')
            ->getQuery()
            ->getArrayResult();

        foreach ($ownedResult as $ownedResultItem) {
            $userId = $ownedResultItem['userId'];
            $gameId = $ownedResultItem['gameId'];
            $active = $ownedResultItem['active'];

            $games[ $gameId ]->addOwnedBy($userId, $active);
        }

        return [
            // to prevent sorting by number keys in JSON
            'games' =>array_values($games),
            'curPageNumber' => $this->pageNumber,
            'maxPageNumber' => $maxPageNumber
        ];

    }
}
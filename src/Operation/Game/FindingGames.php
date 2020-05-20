<?php

namespace App\Operation\Game;

use App\Entity\Game;
use App\Operation\BaseOperation;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Tools\Pagination\Paginator;

class FindingGames extends BaseOperation
{

    /** @var string */
    protected $searchParam;

    /** @var int */
    protected $pageNumber = 1;

    /** @var int */
    protected $itemsPerPage = 5;

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
     */
    public function execute()
    {
        if (!$this->searchParam)
        {
            return [];
        }

        $games = [];

        // first, find strict occurence of name.
        // then, the rest

        $query = trim($this->searchParam);

        $queryBuilder = $this->em->createQueryBuilder()
            ->from(Game::class, 'g')
            ->select('g', 'case when g.name = :query then 1 else 0 end as priority')
            ->setParameter('query', $query)
            ->orderBy('priority', Criteria::DESC)
            ->addOrderBy('g.id', Criteria::ASC)
            ->setMaxResults(50)
            ->addOrderBy('g.id');

        $ids = preg_split('/\s+/', $query);

        $searchExpressions = [
            'title' => $queryBuilder->expr()->like('g.name', ':qTitle'),
            'ids' => $queryBuilder->expr()->in('g.id', ':IDs'),
        ];

        if (preg_match('~[A-Za-z]~', $query)) {
            unset($searchExpressions['ids']);
        }

        $queryBuilder->andWhere(
            count($searchExpressions) === 1
                ? reset($searchExpressions)
                : $queryBuilder->expr()->orX($searchExpressions['title'], $searchExpressions['ids'])
        );

        if (isset($searchExpressions['ids'])) {
            $queryBuilder->setParameter('IDs', $ids);
        }

        if (isset($searchExpressions['title'])) {
            $queryBuilder->setParameter('qTitle', "%{$query}%");
        }

        $paginator = new Paginator($queryBuilder->getQuery());

        $paginator->getQuery()
            ->setFirstResult($this->itemsPerPage*($this->pageNumber - 1))
            ->setMaxResults($this->itemsPerPage);

        /** @var Game[] $games */
        $gamesResult = $paginator->getIterator();

        $totalCount = $paginator->count();
        $maxPageNumber = ceil($totalCount / $this->itemsPerPage);

        foreach ($gamesResult as [$game]) {
            $gameId = $game->getId();

            $games[ $gameId ] = $game;
        }

        return [
            // to prevent sorting by number keys in JSON
            'games' => array_values($games),
            'curPageNumber' => $this->pageNumber,
            'maxPageNumber' => $maxPageNumber
        ];
    }
}
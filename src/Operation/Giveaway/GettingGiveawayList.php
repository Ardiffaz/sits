<?php

namespace App\Operation\Giveaway;

use App\Entity\Giveaway;
use App\Operation\BaseOperation;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Tools\Pagination\Paginator;

class GettingGiveawayList extends BaseOperation
{
    /** @var array */
    protected $filter;

    /** @var int */
    protected $pageNumber = 1;

    /** @var int */
    protected $itemsPerPage = 50;

    /** @var string */
    protected $sortParam;

    /** @var string */
    protected $sortDir = Criteria::DESC;

    public function setFilter($filter)
    {
        $this->filter = $filter;
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

    public function setSortParam(string $sortParam)
    {
        $this->sortParam = $sortParam;
        return $this;
    }

    public function setSortDir(string $sortDir)
    {
        $this->sortDir = strtoupper($sortDir) === Criteria::ASC ? Criteria::ASC : Criteria::DESC;
        return $this;
    }

    public function execute()
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->from(Giveaway::class, 'ga')
            ->select('ga')
            ->leftJoin('ga.gifts', 'gift')
            ->leftJoin('gift.game', 'game');

        $link = isset($this->filter['link']) ? $this->filter['link'] : '';
        if ($link !== '')
        {
            $queryBuilder
                ->andWhere('ga.link like :link')
                ->setParameter('link', '%'.$link.'%');
        }

        $creatorId = isset($this->filter['creator']) ? (int)$this->filter['creator'] : '';
        if ($creatorId > 0)
        {
            $queryBuilder
                ->andWhere('ga.user = :creator')
                ->setParameter('creator', $creatorId);
        }

        $finished = isset($this->filter['finished']) ? $this->filter['finished'] : '';
        if ($finished !== '')
        {
            $finishedValue = $finished == 1 ? 'true' : 'false';
            $queryBuilder
                ->andWhere('ga.successful = '.$finishedValue);
        }

        $game = isset($this->filter['game']) ? $this->filter['game'] : '';
        if ($game !== '')
        {
            if ((int)$game > 0)
            {
                $queryBuilder
                    ->andWhere(
                        $queryBuilder->expr()->orX('game.name like :gameString', 'game.id = :gameInt')
                    )
                    ->setParameter('gameString', '%'.$game.'%')
                    ->setParameter('gameInt', (int)$game);
            }
            else
            {
                $queryBuilder
                    ->andWhere('game.name like :gameString')
                    ->setParameter('gameString', '%'.$game.'%');
            }
        }

        $key = isset($this->filter['key']) ? $this->filter['key'] : '';
        if ($key !== '')
        {
            $queryBuilder
                ->andWhere('gift.key like :key')
                ->setParameter('key', '%'.$key.'%');
        }

        $notes = isset($this->filter['notes']) ? $this->filter['notes'] : '';
        if ($notes !== '')
        {
            $notesArray = explode(' ', trim($notes));
            $orXs = [];

            foreach ($notesArray as $key => $notesItem) {
                $orXs[] = 'ga.notes like :notes'.$key;

                $queryBuilder->setParameter('notes'.$key, '% '.$notesItem.' %');
            }

            $queryBuilder
                ->andWhere(
                    $queryBuilder->expr()->orX(...$orXs)
                );
        }

        if ($this->sortParam === 'date')
        {
            $queryBuilder
                ->orderBy('ga.dateEnded', $this->sortDir)
                ->addOrderBy('ga.link', $this->sortDir)
                ->addOrderBy('ga.id', Criteria::DESC);
        }
        elseif($this->sortParam === 'id')
        {
            $queryBuilder
                ->orderBy('ga.id', $this->sortDir);
        }

        $paginator = new Paginator($queryBuilder->getQuery());

        $paginator->getQuery()
            ->setFirstResult($this->itemsPerPage*($this->pageNumber - 1))
            ->setMaxResults($this->itemsPerPage);

        /** @var Giveaway[] $giveaways */
        $giveaways = $paginator->getIterator();

        $totalCount = $paginator->count();
        $maxPageNumber = ceil($totalCount / $this->itemsPerPage);

        return [
            // to prevent sorting by number keys in JSON
            'giveaways' => $giveaways,
            'curPageNumber' => $this->pageNumber,
            'maxPageNumber' => $maxPageNumber,
            'totalItemsCount' => $totalCount
        ];
    }

}
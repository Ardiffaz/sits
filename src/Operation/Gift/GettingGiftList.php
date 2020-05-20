<?php

namespace App\Operation\Gift;

use App\Entity\Gift;
use App\Operation\BaseOperation;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;

class GettingGiftList extends BaseOperation
{
    public const TYPE_ALL = 'all';
    public const TYPE_FREE = 'free';

    /** @var string */
    protected $type = self::TYPE_ALL;

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

    public function setTypeFree()
    {
        $this->type = self::TYPE_FREE;
        return $this;
    }

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

    /**
     * @return mixed
     * @throws Exception
     */
    public function execute()
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->from(Gift::class, 'gift')
            ->select('gift')
            ->orderBy('gift.id')
            ->leftJoin('gift.game', 'game')
        ;

        if ($this->type === self::TYPE_FREE)
        {
            $queryBuilder
                ->andWhere('gift.giveaway is null');
        }

        $key = isset($this->filter['key']) ? $this->filter['key'] : '';
        if ($key !== '')
        {
            $queryBuilder
                ->andWhere('gift.key like :key')
                ->setParameter('key', '%'.$key.'%');
        }

        $source = isset($this->filter['source']) ? $this->filter['source'] : '';
        if ($source !== '')
        {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->orX('gift.sourceLink like :source', 'gift.sourceName like :source'))
                ->setParameter('source', '%'.$source.'%');
        }

        $lts =  isset($this->filter['lts']) ? $this->filter['lts'] : '';
        if ($lts !== '')
        {
            $trustedSources = ['fanatical', 'indiegala', 'HB', 'humblebundle', 'steampay', 'igromagaz', 'zaka-zaka'];

            if ($lts == 1)
            {
                foreach ($trustedSources as $key => $sourceText) {
                    $queryBuilder
                        ->andWhere('gift.sourceLink not like :source'.$key)
                        ->andWhere('gift.sourceName not like :source'.$key)
                        ->setParameter('source'.$key, '%'.$sourceText.'%');
                }
            }
            else
            {
                $orXs = [];
                foreach ($trustedSources as $key => $sourceText) {
                    $orXs[] = 'gift.sourceLink like :source'.$key;
                    $orXs[] = 'gift.sourceName like :source'.$key;

                    $queryBuilder->setParameter('source'.$key, '%'.$sourceText.'%');
                }

                $queryBuilder
                    ->andWhere(
                        $queryBuilder->expr()->orX(...$orXs)
                    );
            }
        }

        $reservedId = isset($this->filter['reserved']) ? (int)$this->filter['reserved'] : '';
        if ($reservedId > 0)
        {
            $queryBuilder
                ->andWhere('gift.reservedBy = :reservedBy')
                ->setParameter('reservedBy', $reservedId);
        }

        $notes = isset($this->filter['notes']) ? $this->filter['notes'] : '';
        if ($notes !== '')
        {
            $notesArray = explode(' ', trim($notes));
            $orXs = [];

            foreach ($notesArray as $key => $notesItem) {
                $orXs[] = 'gift.notes like :notes'.$key;

                $queryBuilder->setParameter('notes'.$key, '% '.$notesItem.' %');
            }

            $queryBuilder
                ->andWhere(
                    $queryBuilder->expr()->orX(...$orXs)
                );
        }

        $excludedNotes = isset($this->filter['exnotes']) ? $this->filter['exnotes'] : '';
        if ($excludedNotes !== '')
        {
            $excludedNotesArray = explode(' ', trim($excludedNotes));
            foreach ($excludedNotesArray as $key => $excludedNotesItem) {
                $queryBuilder->andWhere('gift.notes not like :exnotes'.$key);
                $queryBuilder->setParameter('exnotes'.$key, '% '.$excludedNotesItem.' %');
            }
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

        $priceFrom = isset($this->filter['price_from']) ? (int)$this->filter['price_from'] : 0;
        if ($priceFrom > 0)
        {
            $queryBuilder
                ->andWhere('ROUND(game.priceUsd) >= :priceFrom')
                ->setParameter('priceFrom', $priceFrom);

        }

        $priceTo = isset($this->filter['price_to']) ? (int)$this->filter['price_to'] : 0;
        if ($priceTo > 0)
        {
            $queryBuilder
                ->andWhere('ROUND(game.priceUsd) <= :priceTo')
                ->setParameter('priceTo', $priceTo);
        }

        $quality = isset($this->filter['quality']) ? $this->filter['quality'] : '';
        if ($quality !== '')
        {
            if ($quality === 'm')
            {
                $queryBuilder
                    ->andWhere('game.reviewCount >= 30')
                    ->andWhere('game.rating >= 75');
            }
            elseif ($quality === 'q')
            {
                $queryBuilder
                    ->andWhere(
                        $queryBuilder->expr()->orX(
                            $queryBuilder->expr()->andX('game.reviewCount >= 1000', 'game.rating >= 70'),
                            $queryBuilder->expr()->andX('game.reviewCount >= 500', 'game.rating >= 75'),
                            $queryBuilder->expr()->andX('game.reviewCount >= 100', 'game.rating >= 80'),
                            $queryBuilder->expr()->andX('game.reviewCount >= 50', 'game.rating >= 85')
                        )
                    );
            }
            elseif ($quality === 'hq')
            {
                $queryBuilder
                    ->andWhere(
                        $queryBuilder->expr()->orX(
                            $queryBuilder->expr()->andX('game.reviewCount >= 1000', 'game.rating >= 80'),
                            $queryBuilder->expr()->andX('game.reviewCount >= 500', 'game.rating >= 85'),
                            $queryBuilder->expr()->andX('game.reviewCount >= 100', 'game.rating >= 90'),
                            $queryBuilder->expr()->andX('game.reviewCount >= 50', 'game.rating >= 95')
                        )
                    );
            }
            elseif ($quality === 'ds')
            {
                $queryBuilder
                    ->andWhere('game.reviewCount >= 10')
                    ->andWhere('game.rating < 70');
            }
            elseif ($quality === 'u')
            {
                $queryBuilder
                    ->andWhere('game.reviewCount < 10');
            }
        }

        $cards = isset($this->filter['cards']) ? $this->filter['cards'] : '';
        if ($cards !== '')
        {
            $cardsValue = $cards == 1 ? 'true' : 'false';

            $queryBuilder
                ->andWhere('game.hasTradingCards = '.$cardsValue);
        }

        $achievements = isset($this->filter['achievements']) ? $this->filter['achievements'] : '';
        if ($achievements !== '')
        {
            if ($achievements == 1)
            {
                $queryBuilder
                    ->andWhere('game.achievementCount > 0');
            }
            else
            {
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->orX('game.achievementCount = 0', 'game.achievementCount is null'));
            }
        }

        if ($this->sortParam === 'price')
        {
            $queryBuilder
                ->orderBy('game.priceUsd', $this->sortDir)
                ->addOrderBy('game.name', Criteria::ASC)
                ->addOrderBy('gift.id', Criteria::ASC);
        }
        elseif ($this->sortParam === 'rating')
        {
            $queryBuilder
                ->orderBy('game.rating', $this->sortDir)
                ->addOrderBy('game.name', Criteria::ASC)
                ->addOrderBy('gift.id', Criteria::ASC);
        }
        elseif($this->sortParam === 'count')
        {
            $queryBuilder
                ->addSelect(
                    '('.
                        $this->em->createQueryBuilder()
                            ->from(Gift::class, 'groupedGift')
                            ->select('count(identity(groupedGift.game))')
                            ->where('identity(groupedGift.game) = game.id')
                            ->getDQL()
                    .') as HIDDEN gameCnt'
                )
                ->orderBy('gameCnt', $this->sortDir)
                ->addOrderBy('gift.id', Criteria::ASC);
        }

        $paginator = new Paginator($queryBuilder->getQuery());

        $paginator->getQuery()
            ->setFirstResult($this->itemsPerPage*($this->pageNumber - 1))
            ->setMaxResults($this->itemsPerPage);

        /** @var Gift[] $gifts */
        $gifts = $paginator->getIterator();

        $totalCount = $paginator->count();
        $maxPageNumber = ceil($totalCount / $this->itemsPerPage);

        return [
            // to prevent sorting by number keys in JSON
            'gifts' => $gifts,
            'curPageNumber' => $this->pageNumber,
            'maxPageNumber' => $maxPageNumber
        ];
    }
}
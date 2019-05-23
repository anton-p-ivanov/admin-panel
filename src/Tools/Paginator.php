<?php

namespace App\Tools;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

/**
 * Class Paginator
 * @package App\Tools
 */
class Paginator extends \Doctrine\ORM\Tools\Pagination\Paginator
{
    /**
     * @var int
     */
    private $resultsTotal = 0;

    /**
     * @var int|null
     */
    private $resultsPerPage = 20;

    /**
     * @var int
     */
    private $pagesTotal = 1;

    /**
     * @var int
     */
    private $pagesMax = 7;

    /**
     * @var array
     */
    private $pagesRange = [];

    /**
     * @var int
     */
    private $page = 1;

    /**
     * Paginator constructor.
     *
     * @param Query|QueryBuilder $query
     * @param int $currentPage
     * @param int|null $resultsPerPage
     * @param bool $fetchJoinCollection
     */
    public function __construct($query, $currentPage = 1, $resultsPerPage = null, $fetchJoinCollection = true)
    {
        parent::__construct($query, $fetchJoinCollection);

        $this->page = $currentPage;
        $this->resultsTotal = count($this);

        if (is_int($resultsPerPage) && $resultsPerPage > 1) {
            $this->resultsPerPage = $resultsPerPage;
        }

        $this->build($query);
    }

    /**
     * @param Query|QueryBuilder $query
     */
    public function build($query)
    {
        $query->setFirstResult($this->resultsPerPage * ($this->page - 1));
        $query->setMaxResults($this->resultsPerPage);

        if ($this->resultsPerPage && $this->resultsTotal) {
            $this->pagesTotal = ceil($this->resultsTotal / $this->resultsPerPage);
        }

        if ($this->page > $this->pagesTotal) {
            throw new \InvalidArgumentException('Invalid page.');
        }

        $this->setPagesRange();
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getResultsPerPage(): int
    {
        return $this->resultsPerPage;
    }

    /**
     * @return int
     */
    public function getResulsTotal(): int
    {
        return $this->resultsTotal;
    }

    /**
     * @return int
     */
    public function getPagesTotal(): int
    {
        return $this->pagesTotal;
    }

    /**
     * @return array
     */
    public function getPagesRange(): array
    {
        return $this->pagesRange;
    }

    /**
     * @return Paginator
     */
    public function setPagesRange(): self
    {
        $min = 1;
        $max = $this->pagesTotal;

        if ($this->pagesTotal > $this->pagesMax) {
            if ($this->page < $this->pagesMax) {
                $max = $this->pagesMax;
            }
            else if ($this->page > $this->pagesTotal - $this->pagesMax + 1) {
                $min = $this->pagesTotal - $this->pagesMax + 1;
            }
            else {
                $min = $this->page - floor($this->pagesMax / 2);
                $max = $this->page + floor($this->pagesMax / 2);
            }
        }

        $this->pagesRange = [
            'min' => $min,
            'max' => $max
        ];

        return $this;
    }

    /**
     * @return int
     */
    public function getFirstIndex(): int
    {
        return $this->resultsPerPage * ($this->page - 1) + 1;
    }

    /**
     * @return int
     */
    public function getLastIndex(): int
    {
        $last = $this->resultsPerPage * $this->page;

        if ($this->resultsTotal < $last) {
            $last = $this->resultsTotal;
        }

        return $last;
    }
}
<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class WorkflowRepository
 *
 * @package App\Repository
 */
class WorkflowRepository extends EntityRepository
{
    /**
     * @param array $items
     *
     * @return \Doctrine\ORM\Query
     */
    public function getRemoveAllQuery($items = [])
    {
        $qb = $this->createQueryBuilder('t');

        return $qb
            ->delete(['t'])
            ->where($qb->expr()->in('uuid', ':items'))
            ->setParameter('items', $items)
            ->getQuery();
    }
}

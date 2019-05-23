<?php

namespace App\Repository;

use App\Entity\Site;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class SiteRepository
 * @package App\Repository
 */
class SiteRepository extends ServiceEntityRepository
{
    /**
     * SiteRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Site::class);
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    public function search()
    {
        return $this->getAvailableQueryBuilder()->getQuery();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAvailableQueryBuilder()
    {
        $queryBuilder = $this->createQueryBuilder('s');

        return $queryBuilder
            ->select(['s', 'w'])
            ->leftJoin('s.workflow', 'w')
            ->where('w.uuid IS NULL OR w.isDeleted = :isDeleted')
            ->setParameters(['isDeleted' => false])
            ->addOrderBy('s.sort', 'ASC')
            ->addOrderBy('s.title', 'ASC');
    }
}

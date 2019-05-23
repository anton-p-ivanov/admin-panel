<?php

namespace App\Repository;

use App\Entity\SalesDiscount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class SalesDiscountRepository
 *
 * @package App\Repository
 */
class SalesDiscountRepository extends ServiceEntityRepository
{
    /**
     * SalesDiscountRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SalesDiscount::class);
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    public function search()
    {
        return $this->createQueryBuilder('t0')
            ->select(['t0'])
            ->addOrderBy('t0.sort', 'ASC')
            ->getQuery();
    }
}

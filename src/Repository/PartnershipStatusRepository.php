<?php

namespace App\Repository;

use App\Entity\PartnershipStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class PartnershipStatusRepository
 *
 * @package App\Repository
 */
class PartnershipStatusRepository extends ServiceEntityRepository
{
    /**
     * PartnershipStatusRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PartnershipStatus::class);
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

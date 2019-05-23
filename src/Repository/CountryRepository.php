<?php

namespace App\Repository;

use App\Entity\Country;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class CountryRepository
 * @package App\Repository
 */
class CountryRepository extends ServiceEntityRepository
{
    /**
     * CountryRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Country::class);
    }

    /**
     * @return mixed
     */
    public function getAvailable()
    {
        $queryBuilder = $this->createQueryBuilder('t');

        return $queryBuilder
            ->select('t')
            ->addOrderBy('t.title', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

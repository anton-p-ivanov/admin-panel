<?php

namespace App\Repository\Price;

use App\Entity\Price\Discount;
use App\Entity\Price\Price;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class DiscountRepository
 *
 * @package App\Repository\Price
 */
class DiscountRepository extends ServiceEntityRepository
{
    /**
     * DiscountRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Discount::class);
    }

    /**
     * @param Price $price
     *
     * @return \Doctrine\ORM\Query
     */
    public function search(Price $price)
    {
        $queryBuilder = $this->createQueryBuilder('d');

        return $queryBuilder
            ->select(['d', 'p'])
            ->innerJoin('d.price', 'p')
            ->where('d.price = :price')
            ->setParameter('price', $price)
            ->getQuery();
    }
}

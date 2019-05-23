<?php

namespace App\Repository\Account;

use App\Entity\Account\Discount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class DiscountRepository
 *
 * @package App\Repository
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
     * @param array $params
     *
     * @return \Doctrine\ORM\Query
     */
    public function search(array $params = []): \Doctrine\ORM\Query
    {
        $qb = $this->createQueryBuilder('t0');

        $predicates = $qb->expr()->andX();

        foreach ($params as $key => $value) {
            $key = lcfirst(implode(array_map('ucfirst', preg_split('/_/', $key))));
            if ($value && property_exists($this->getEntityName(), $key)) {
                $predicates->add($qb->expr()->eq("t0.$key", ":$key"));
                $qb->setParameter($key, $value);
            }
        }

        return $qb
            ->select(['t0', 't1', 't2'])
            ->innerJoin('t0.discount', 't1')
            ->innerJoin('t0.account', 't2')
            ->where($predicates)
            ->orderBy('t1.sort', 'ASC')
            ->getQuery();
    }
}

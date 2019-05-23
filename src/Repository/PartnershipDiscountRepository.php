<?php

namespace App\Repository;

use App\Entity\PartnershipDiscount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class PartnershipDiscountRepository
 *
 * @package App\Repository
 */
class PartnershipDiscountRepository extends ServiceEntityRepository
{
    /**
     * PartnershipDiscountRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PartnershipDiscount::class);
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
            ->innerJoin('t0.status', 't2')
            ->where($predicates)
            ->orderBy('t1.sort', 'ASC')
            ->getQuery();
    }
}

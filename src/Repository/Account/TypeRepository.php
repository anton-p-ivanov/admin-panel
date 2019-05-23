<?php

namespace App\Repository\Account;

use App\Entity\Account\Type;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class TypeRepository
 *
 * @package App\Repository\Account
 */
class TypeRepository extends ServiceEntityRepository
{
    /**
     * TypeRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Type::class);
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    public function search()
    {
        $queryBuilder = $this->createQueryBuilder('t');

        return $queryBuilder
            ->select(['t'])
            ->addOrderBy('t.sort', 'ASC')
            ->addOrderBy('t.title', 'ASC')
            ->getQuery();
    }

    /**
     * @return mixed
     */
    public function disableDefaultStatus()
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        return $queryBuilder->update('App:Account\Type', 't')
            ->set('t.isDefault', 0)
            ->where('t.isDefault = :value')
            ->setParameter('value', 1)
            ->getQuery()
            ->execute();
    }
}

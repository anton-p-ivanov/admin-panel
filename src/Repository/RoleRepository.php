<?php

namespace App\Repository;

use App\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class RoleRepository
 *
 * @package App\Repository
 */
class RoleRepository extends ServiceEntityRepository
{
    /**
     * RoleRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Role::class);
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
            ->select(['s'])
            ->addOrderBy('s.title', 'ASC');
    }

    /**
     * @return mixed
     */
    public function disableDefaultStatus()
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        return $queryBuilder->update('App:Role', 'c')
            ->set('c.isDefault', 0)
            ->where('c.isDefault = :value')
            ->setParameter('value', 1)
            ->getQuery()
            ->execute();
    }
}

<?php

namespace App\Repository;

use App\Entity\WorkflowStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class WorkflowStatusRepository
 *
 * @package App\Repository
 */
class WorkflowStatusRepository extends ServiceEntityRepository
{
    /**
     * UserCheckwordRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, WorkflowStatus::class);
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    public function search()
    {
        $queryBuilder = $this->createQueryBuilder('status');

        return $queryBuilder
            ->select(['status'])
            ->orderBy('status.title', 'ASC')
            ->getQuery();
    }

    /**
     * @return mixed
     */
    public function disableDefaultStatus()
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        return $queryBuilder->update('App:WorkflowStatus', 'c')
            ->set('c.isDefault', 0)
            ->where('c.isDefault = :value')
            ->setParameter('value', 1)
            ->getQuery()
            ->execute();
    }

    /**
     * @return null|object|WorkflowStatus
     */
    public function getDefault()
    {
        return $this->findOneBy(['isDefault' => true]);
    }
}

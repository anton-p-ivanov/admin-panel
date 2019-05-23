<?php

namespace App\Repository;

use App\Entity\AddressType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class AddressTypeRepository
 *
 * @package App\Repository
 */
class AddressTypeRepository extends ServiceEntityRepository
{
    /**
     * AddressTypeRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AddressType::class);
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    public function search()
    {
        $queryBuilder = $this->createQueryBuilder('t');

        return $queryBuilder
            ->select(['t', 'w'])
            ->leftJoin('t.workflow', 'w')
            ->where('w.uuid IS NULL OR w.isDeleted = :isDeleted')
            ->setParameter('isDeleted', false)
            ->orderBy('w.updatedAt', 'DESC')
            ->getQuery();
    }

    /**
     * @param AddressType|null $except
     *
     * @return mixed
     */
    public function disableDefaultStatus(AddressType $except = null)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->update('App:AddressType', 't')
            ->set('t.isDefault', 0)
            ->where('t.isDefault = :value')
            ->setParameter('value', 1);

        if ($except) {
            $queryBuilder->andWhere($queryBuilder->expr()->neq('t.uuid', ':except'));
            $queryBuilder->setParameter('except', $except->getUuid());
        }

        return $queryBuilder->getQuery()
            ->execute();
    }
}

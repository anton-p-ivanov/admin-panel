<?php

namespace App\Repository\Storage;

use App\Entity\Storage\Storage;
use App\Entity\Storage\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class StorageVersionRepository
 *
 * @package App\Repository\Storage
 */
class StorageVersionRepository extends ServiceEntityRepository
{
    /**
     * StorageVersionRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Version::class);
    }

    /**
     * @param array $params
     *
     * @return \Doctrine\ORM\Query
     */
    public function search($params = [])
    {
        $qb = $this->createQueryBuilder('t');

        $predicates = $qb->expr()->andX();
        foreach ($params as $name => $value) {
            $predicates->add($qb->expr()->eq("t.$name", ":$name"));
            $qb->setParameter($name, $value);
        }

        return $qb
            ->select(['t', 'w', 'f'])
            ->leftJoin('t.file', 'f')
            ->leftJoin('f.workflow', 'w')
            ->where($predicates)
            ->addOrderBy('w.updatedAt', 'DESC')
            ->getQuery();
    }

    /**
     * @param Storage $storage
     *
     * @return mixed
     */
    public function expireVersions(Storage $storage)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        return $queryBuilder->update('App:Storage\Version', 't')
            ->set('t.isActive', 0)
            ->andWhere($queryBuilder->expr()->eq('t.isActive', ':isActive'))
            ->andWhere($queryBuilder->expr()->eq('t.storage', ':storage'))
            ->setParameter(':isActive', 1)
            ->setParameter(':storage', $storage)
            ->getQuery()
            ->execute();
    }
}

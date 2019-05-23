<?php

namespace App\Repository\Field;

use App\Entity\Field\Field;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class FieldRepository
 *
 * @package App\Repository\Field
 */
class FieldRepository extends ServiceEntityRepository
{
    /**
     * FieldRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Field::class);
    }

    /**
     * @param string $hash
     *
     * @return \Doctrine\ORM\Query
     */
    public function search(string $hash)
    {
        $qb = $this->createQueryBuilder('t');

        $params = ['isDeleted' => false, 'hash' => $hash];
        $predicates = $qb->expr()->andX();
        $predicates->add('w.uuid IS NULL OR w.isDeleted = :isDeleted');
        $predicates->add('t.hash = :hash');

        return $qb
            ->select(['t', 'w'])
            ->leftJoin('t.workflow', 'w')
            ->where($predicates)
            ->setParameters($params)
            ->addOrderBy('t.sort', 'ASC')
            ->addOrderBy('t.label', 'ASC')
            ->getQuery();
    }

    /**
     * @param string $hash
     *
     * @return mixed
     */
    public function findAllAvailableByHash(string $hash)
    {
        $qb = $this->createQueryBuilder('t');

        $predicates = $qb->expr()->andX();
        $predicates->add('w.uuid IS NULL OR w.isDeleted = :isDeleted');
        $predicates->add('t.isActive = :isActive');
        $predicates->add('t.hash = :hash');

        return $qb
            ->select(['t', 'w', 'v'])
            ->leftJoin('t.workflow', 'w')
            ->leftJoin('t.values', 'v')
            ->where($predicates)
            ->setParameters([
                'isDeleted' => false,
                'isActive' => true,
                'hash' => $hash
            ])
            ->addOrderBy('t.sort', 'ASC')
            ->addOrderBy('t.label', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

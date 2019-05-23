<?php

namespace App\Repository\Catalog;

use App\Entity\Catalog\Type;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class TypeRepository
 *
 * @package App\Repository\Field
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
     * @param array $conditions
     *
     * @return \Doctrine\ORM\Query
     */
    public function search($conditions = [])
    {
        $qb = $this->createQueryBuilder('t');

        $params = ['isDeleted' => false];
        $predicates = $qb->expr()->andX();
        $predicates->add('w.uuid IS NULL OR w.isDeleted = :isDeleted');
        foreach ($conditions as $key => $value) {
            if ($value && property_exists($this->getEntityName(), $key)) {
                $predicates->add($qb->expr()->eq("t.$key", ":$key"));
                $params[$key] = $value;
            }
        }

        return $qb
            ->select(['t', 'w'])
            ->leftJoin('t.workflow', 'w')
            ->where($predicates)
            ->setParameters($params)
            ->getQuery();
    }
}

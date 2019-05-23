<?php

namespace App\Repository\Catalog;

use App\Entity\Catalog\Catalog;
use App\Entity\Field\Field;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class CatalogRepository
 *
 * @package App\Repository\Catalog
 */
class CatalogRepository extends ServiceEntityRepository
{
    /**
     * CatalogRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Catalog::class);
    }

    /**
     * @param array $conditions
     *
     * @return \Doctrine\ORM\Query
     */
    public function search($conditions = [])
    {
        $qb = $this->createQueryBuilder('c');

        $params = ['isDeleted' => false];
        $predicates = $qb->expr()->andX();
        $predicates->add('w.uuid IS NULL OR w.isDeleted = :isDeleted');
        foreach ($conditions as $key => $value) {
            if ($value && property_exists($this->getEntityName(), $key)) {
                $predicates->add($qb->expr()->eq("c.$key", ":$key"));
                $params[$key] = $value;
            }
        }

        return $qb
            ->select(['c', 'w'/*, 'e'*/])
            ->leftJoin('c.workflow', 'w')
//            ->leftJoin('c.elements', 'e')
            ->where($predicates)
//            ->groupBy('e.catalog')
            ->setParameters($params)
            ->getQuery();
    }

    /**
     * @param Field $field
     *
     * @return Catalog|null
     */
    public function findByField(Field $field)
    {
        $queryBuilder = $this->createQueryBuilder('t');

        return $queryBuilder
            ->innerJoin('t.fields', 'f')
            ->where($queryBuilder->expr()->eq('f.uuid', ':field'))
            ->setParameter('field', $field)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

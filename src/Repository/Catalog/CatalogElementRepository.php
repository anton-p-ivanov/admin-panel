<?php

namespace App\Repository\Catalog;

use App\Entity\Catalog\Catalog;
use App\Entity\Catalog\Element;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class CatalogElementRepository
 *
 * @package App\Repository\Catalog
 */
class CatalogElementRepository extends ServiceEntityRepository
{
    /**
     * CatalogElementRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Element::class);
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

    /**
     * @param Catalog $catalog
     *
     * @return mixed
     */
    public function getAvailableElementsByCatalog(Catalog $catalog)
    {
        $qb = $this->createQueryBuilder('t');

        $params = [
            'isDeleted' => false,
            'isActive' => true,
            'catalog' => $catalog,
            'type' => Element::TYPE_ELEMENT,
            'status' => 'PUBLISHED'
        ];

        $predicates = $qb->expr()->andX();
        $predicates->add('w.uuid IS NULL OR w.isDeleted = :isDeleted');
        $predicates->add($qb->expr()->eq('t.isActive', ':isActive'));
        $predicates->add($qb->expr()->eq('t.catalog', ':catalog'));
        $predicates->add($qb->expr()->eq('t.type', ':type'));
        $predicates->add($qb->expr()->eq('s.code', ':status'));

        return $qb
            ->select(['t.title', 't.uuid'])
            ->leftJoin('t.workflow', 'w')
            ->innerJoin('w.status', 's')
            ->where($predicates)
            ->setParameters($params)
            ->addOrderBy('t.sort', 'ASC')
            ->addOrderBy('t.title', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

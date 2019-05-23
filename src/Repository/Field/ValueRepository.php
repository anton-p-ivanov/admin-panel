<?php

namespace App\Repository\Field;

use App\Entity\Field\Value;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class ValueRepository
 *
 * @package App\Repository\Field
 */
class ValueRepository extends ServiceEntityRepository
{
    /**
     * ValueRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Value::class);
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

        foreach ($params as $key => $value) {
            if ($value && property_exists($this->getEntityName(), $key)) {
                $predicates->add($qb->expr()->eq("t.$key", ":$key"));
                $qb->setParameter($key, $value);
            }
        }

        return $qb
            ->select(['t'])
            ->where($predicates)
            ->getQuery();
    }
}

<?php

namespace App\Repository\Account;

use App\Entity\Account\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class StatusRepository
 *
 * @package App\Repository\Account
 */
class StatusRepository extends ServiceEntityRepository
{
    /**
     * StatusRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Status::class);
    }

    /**
     * @param array $params
     *
     * @return \Doctrine\ORM\Query
     */
    public function search($params = [])
    {
        $qb = $this->createQueryBuilder('t0');

        $predicates = $qb->expr()->andX();

        foreach ($params as $key => $value) {
            $key = lcfirst(implode(array_map('ucfirst', preg_split('/_/', $key))));
            if ($value && property_exists($this->getEntityName(), $key)) {
                $predicates->add($qb->expr()->eq("t0.$key", ":$key"));
                $qb->setParameter($key, $value);
            }
        }

        return $qb
            ->select(['t0', 't1'])
            ->innerJoin('t0.status', 't1')
            ->where($predicates)
            ->getQuery();
    }
}

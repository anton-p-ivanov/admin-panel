<?php

namespace App\Repository\Training;

use App\Entity\Training\Attempt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class AttemptRepository
 *
 * @package App\Repository\Training
 */
class AttemptRepository extends ServiceEntityRepository
{
    /**
     * AttemptRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Attempt::class);
    }

    /**
     * @param array $params
     *
     * @return \Doctrine\ORM\Query
     */
    public function search($params = []): \Doctrine\ORM\Query
    {
        $qb = $this->createQueryBuilder('attempt');

        $predicates = $qb->expr()->andX();

        foreach ($params as $key => $value) {
            $key = lcfirst(implode(array_map('ucfirst', preg_split('/_/', $key))));
            if ($value && property_exists($this->getEntityName(), $key)) {
                $predicates->add($qb->expr()->eq("attempt.$key", ":$key"));
                $qb->setParameter($key, $value);
            }
        }

        return $qb
            ->select(['attempt', 'user'])
            ->where($predicates)
            ->orderBy('attempt.startedAt', 'DESC')
            ->innerJoin('attempt.user', 'user')
            ->getQuery();
    }
}

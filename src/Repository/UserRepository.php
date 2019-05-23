<?php

namespace App\Repository;

use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class UserRepository
 * @package App\Repository
 */
class UserRepository extends ServiceEntityRepository
{
    /**
     * UserRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param string $value
     * @return User|null
     */
    public function findOneByEmail(string $value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param null|string $search
     *
     * @return \Doctrine\ORM\Query
     */
    public function search(?string $search)
    {
        $qb = $this->createQueryBuilder('t');

        $predicates = $qb->expr()->andX();
        $predicates->add('w.uuid IS NULL OR w.isDeleted = :isDeleted');
        $qb->setParameter('isDeleted', false);

        if ($search) {
            $predicates->add('t.fname LIKE :search OR t.lname LIKE :search OR t.email LIKE :search');
            $qb->setParameter('search', "%$search%");
        }

        return $qb
            ->select(['t', 'w'])
            ->leftJoin('t.workflow', 'w')
            ->where($predicates)
            ->addOrderBy('CONCAT(t.fname, t.lname)', 'ASC')
            ->getQuery();
    }

    /**
     * @return mixed
     */
    public function getAllAvailable()
    {
        $queryBuilder = $this->createQueryBuilder('t');

        $predicates = $queryBuilder->expr()->andX();
        $predicates->add($queryBuilder->expr()->eq('t.isActive', ':isActive'));
        $predicates->add($queryBuilder->expr()->eq('w.isDeleted', ':isDeleted'));

        $params = [
            'isActive' => true,
            'isDeleted' => false
        ];

        return $queryBuilder
            ->select('t')
            ->innerJoin('t.workflow', 'w')
            ->where($predicates)
            ->setParameters($params)
            ->addOrderBy('t.fname', 'ASC')
            ->addOrderBy('t.lname', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

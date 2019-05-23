<?php

namespace App\Repository;

use App\Entity\User\User;
use App\Entity\User\Account;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class UserAccountRepository
 * @package App\Repository
 */
class UserAccountRepository extends ServiceEntityRepository
{
    /**
     * UserAccountRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Account::class);
    }

    /**
     * @param User $user
     *
     * @return \Doctrine\ORM\Query
     */
    public function search(User $user)
    {
        $qb = $this->createQueryBuilder('t0');

        return $qb
            ->select(['t0', 't1'])
            ->innerJoin('t0.account', 't1')
            ->where('t0.user = :user')
            ->setParameter('user', $user)
            ->getQuery();
    }
}

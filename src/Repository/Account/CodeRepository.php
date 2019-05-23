<?php

namespace App\Repository\Account;

use App\Entity\Account\Account;
use App\Entity\Account\Code;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class CodeRepository
 *
 * @package App\Repository\Account
 */
class CodeRepository extends ServiceEntityRepository
{
    /**
     * CodeRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Code::class);
    }

    /**
     * @param Account $account
     *
     * @return mixed
     */
    public function expireAccountCodes(Account $account)
    {
        $queryBuilder = $this->createQueryBuilder('t');

        return $queryBuilder
            ->update()
            ->set('t.isExpired', true)
            ->where('t.account = :account')
            ->setParameter('account', $account)
            ->getQuery()
            ->execute();
    }

    /**
     * @param Account $account
     *
     * @return \Doctrine\ORM\Query
     */
    public function search(Account $account)
    {
        $qb = $this->createQueryBuilder('t');

        return $qb
            ->select(['t'])
            ->where('t.account = :account')
            ->setParameter('account', $account)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery();
    }
}

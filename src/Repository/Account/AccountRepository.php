<?php

namespace App\Repository\Account;

use App\Entity\Account\Account;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class AccountRepository
 *
 * @package App\Repository\Account
 */
class AccountRepository extends ServiceEntityRepository
{
    /**
     * AccountRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Account::class);
    }

    /**
     * @param null|string $search
     *
     * @return \Doctrine\ORM\Query
     */
    public function search(?string $search)
    {
        $queryBuilder = $this->createQueryBuilder('t');

        $conditions = $queryBuilder->expr()->andX();
        $conditions->add('w.uuid IS NULL OR w.isDeleted = :isDeleted');
        $queryBuilder->setParameter('isDeleted', false);

        if ($search) {
            $conditions->add('t.title LIKE :search OR t.description LIKE :search OR t.email LIKE :search');
            $queryBuilder->setParameter('search', "%$search%");
        }

        return $queryBuilder
            ->select(['t', 'w'])
            ->leftJoin('t.workflow', 'w')
            ->where($conditions)
            ->orderBy('t.title', 'ASC')
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
            ->addOrderBy('t.sort', 'ASC')
            ->addOrderBy('t.title', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

<?php

namespace App\Repository\Mail;

use App\Entity\Mail\Type;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class TypeRepository
 * @package App\Repository
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
     * @param null|string $search
     *
     * @return \Doctrine\ORM\Query
     */
    public function search(?string $search)
    {
        $queryBuilder = $this->createQueryBuilder('t');

        if ($search) {
            $queryBuilder->andWhere('t.title LIKE :search OR t.description LIKE :search OR t.code LIKE :search');
            $queryBuilder->setParameter('search', "%$search%");
        }

        return $queryBuilder
            ->select(['t', 'w'])
            ->leftJoin('t.workflow', 'w')
            ->andWhere('w.uuid IS NULL OR w.isDeleted = :isDeleted')
            ->setParameter('isDeleted', false)
            ->orderBy('w.updatedAt', 'DESC')
            ->getQuery();
    }
}

<?php

namespace App\Repository\Form;

use App\Entity\Field\Field;
use App\Entity\Form\Form;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class FormRepository
 *
 * @package App\Repository\Form
 */
class FormRepository extends ServiceEntityRepository
{
    /**
     * AccountRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Form::class);
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
            ->innerJoin('t.workflow', 'w')
            ->andWhere('w.uuid IS NULL OR w.isDeleted = :isDeleted')
            ->setParameter('isDeleted', false)
            ->orderBy('w.updatedAt', 'DESC')
            ->getQuery();
    }

    /**
     * @param Field $field
     *
     * @return Form|null
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

<?php

namespace App\Repository\Mail;

use App\Entity\Mail\Template;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class TemplateRepository
 * @package App\Repository
 */
class TemplateRepository extends ServiceEntityRepository
{
    /**
     * TemplateRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Template::class);
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
            $predicates->add('t.code LIKE :search OR t.subject LIKE :search');
            $qb->setParameter('search', "%$search%");
        }

        return $qb
            ->select(['t', 'w'])
            ->innerJoin('t.workflow', 'w')
            ->where($predicates)
            ->orderBy('w.updatedAt', 'DESC')
            ->getQuery();
    }
}

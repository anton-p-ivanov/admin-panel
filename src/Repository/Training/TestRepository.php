<?php

namespace App\Repository\Training;

use App\Entity\Training\Course;
use App\Entity\Training\Test;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class TestRepository
 *
 * @package App\Repository\Training
 */
class TestRepository extends ServiceEntityRepository
{
    /**
     * LessonRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Test::class);
    }

    /**
     * @param Course $course
     *
     * @return \Doctrine\ORM\Query
     */
    public function search(Course $course): \Doctrine\ORM\Query
    {
        $qb = $this->createQueryBuilder('t');

        return $qb
            ->select(['t', 'w'])
            ->innerJoin('t.workflow', 'w')
            ->andWhere('t.course = :course')
            ->andWhere('w.uuid IS NULL OR w.isDeleted = :isDeleted')
            ->setParameters([
                'isDeleted' => false,
                'course' => $course
            ])
            ->orderBy('t.title', 'ASC')
            ->getQuery();
    }
}

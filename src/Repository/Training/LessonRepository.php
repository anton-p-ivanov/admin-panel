<?php

namespace App\Repository\Training;

use App\Entity\Training\Course;
use App\Entity\Training\Lesson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class LessonRepository
 *
 * @package App\Repository\Training
 */
class LessonRepository extends ServiceEntityRepository
{
    /**
     * LessonRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Lesson::class);
    }

    /**
     * @param Course $course
     *
     * @return \Doctrine\ORM\Query
     */
    public function search(Course $course): \Doctrine\ORM\Query
    {
        $qb = $this->createQueryBuilder('l');

        return $qb
            ->select(['l', 'w'])
            ->leftJoin('l.workflow', 'w')
            ->andWhere('l.course = :course')
            ->andWhere('w.uuid IS NULL OR w.isDeleted = :isDeleted')
            ->setParameters([
                'course' => $course,
                'isDeleted' => false
            ])
            ->addOrderBy('l.sort', 'ASC')
            ->addOrderBy('l.title', 'ASC')
            ->getQuery();
    }
}

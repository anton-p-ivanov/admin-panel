<?php

namespace App\Repository\Training;

use App\Entity\Training\Course;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class CourseRepository
 *
 * @package App\Repository\Training
 */
class CourseRepository extends ServiceEntityRepository
{
    /**
     * CourseRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Course::class);
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    public function search()
    {
        $queryBuilder = $this->createQueryBuilder('course');

        return $queryBuilder
            ->select(['course', 'workflow'])
            ->innerJoin('course.workflow', 'workflow')
            ->where($queryBuilder->expr()->eq('workflow.isDeleted', '0'))
            ->orderBy('workflow.updatedAt', 'DESC')
            ->getQuery();
    }
}

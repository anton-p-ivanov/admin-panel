<?php

namespace App\Repository\Training;

use App\Entity\Training\Lesson;
use App\Entity\Training\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class QuestionRepository
 *
 * @package App\Repository\Training
 */
class QuestionRepository extends ServiceEntityRepository
{
    /**
     * QuestionRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Question::class);
    }

    /**
     * @param Lesson $lesson
     *
     * @return \Doctrine\ORM\Query
     */
    public function search(Lesson $lesson): \Doctrine\ORM\Query
    {
        $qb = $this->createQueryBuilder('q');

        return $qb
            ->select(['q', 'w'])
            ->leftJoin('q.workflow', 'w')
            ->andWhere('q.lesson = :lesson')
            ->andWhere('w.uuid IS NULL OR w.isDeleted = :isDeleted')
            ->setParameter('isDeleted', false)
            ->setParameter('lesson', $lesson)
            ->addOrderBy('q.sort', 'ASC')
            ->addOrderBy('q.title', 'ASC')
            ->getQuery();
    }
}

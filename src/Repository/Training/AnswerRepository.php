<?php

namespace App\Repository\Training;

use App\Entity\Training\Answer;
use App\Entity\Training\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class AnswerRepository
 *
 * @package App\Repository\Training
 */
class AnswerRepository extends ServiceEntityRepository
{
    /**
     * AnswerRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Answer::class);
    }

    /**
     * @param Question $question
     *
     * @return \Doctrine\ORM\Query
     */
    public function search(Question $question): \Doctrine\ORM\Query
    {
        $qb = $this->createQueryBuilder('a');

        return $qb
            ->select(['a'])
            ->where('a.question = :question')
            ->setParameter('question', $question)
            ->addOrderBy('a.sort', 'ASC')
            ->addOrderBy('a.answer', 'ASC')
            ->getQuery();
    }

    /**
     * Invalidate all valid answers.
     *
     * @param Question $question
     * @param array $except
     *
     * @return mixed
     */
    public function disableValidAnswers(Question $question, $except = [])
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->update('App:Training\Answer', 'c')
            ->set('c.isValid', ':isValid')
            ->andWhere($qb->expr()->eq('c.isValid', ':isValidCondition'))
            ->andWhere($qb->expr()->eq('c.question', ':question'))
            ->setParameters([
                'isValid' => false,
                'isValidCondition' => true,
                'question' => $question
            ]);

        if ($except) {
            $qb->andWhere($qb->expr()->notIn('c.uuid', ':except'))
                ->setParameter('except', $except);
        }

        return $qb->getQuery()->execute();
    }
}

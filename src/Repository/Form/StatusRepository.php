<?php

namespace App\Repository\Form;

use App\Entity\Form\Form;
use App\Entity\Form\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class StatusRepository
 *
 * @package App\Repository\Form
 */
class StatusRepository extends ServiceEntityRepository
{
    /**
     * StatusRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Status::class);
    }

    /**
     * @param array $params
     *
     * @return \Doctrine\ORM\Query
     */
    public function search($params = [])
    {
        $qb = $this->createQueryBuilder('t');

        $predicates = $qb->expr()->andX();

        foreach ($params as $key => $value) {
            if ($value && property_exists($this->getEntityName(), $key)) {
                $predicates->add($qb->expr()->eq("t.$key", ":$key"));
                $qb->setParameter($key, $value);
            }
        }

        return $qb
            ->select(['t', 'w'])
            ->leftJoin('t.workflow', 'w')
            ->where($predicates)
            ->getQuery();
    }

    /**
     * @param Form $form
     *
     * @return mixed
     */
    public function disableDefaultStatus(Form $form)
    {
        $queryBuilder = $this->createQueryBuilder('t');

        return $queryBuilder->update()
            ->set('t.isDefault', 0)
            ->andWhere($queryBuilder->expr()->eq('t.isDefault', ':value'))
            ->andWhere($queryBuilder->expr()->eq('t.form', ':form'))
            ->setParameter('value', 1)
            ->setParameter('form', $form)
            ->getQuery()
            ->execute();
    }
}

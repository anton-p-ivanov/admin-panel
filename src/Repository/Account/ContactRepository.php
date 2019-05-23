<?php

namespace App\Repository\Account;

use App\Entity\Account\Contact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class ContactRepository
 *
 * @package App\Repository\Account
 */
class ContactRepository extends ServiceEntityRepository
{
    /**
     * ContactRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Contact::class);
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
            $key = lcfirst(implode(array_map('ucfirst', preg_split('/_/', $key))));
            if ($value && property_exists($this->getEntityName(), $key)) {
                $predicates->add($qb->expr()->eq("t.$key", ":$key"));
                $qb->setParameter($key, $value);
            }
        }

        return $qb
            ->select(['t'])
            ->where($predicates)
            ->getQuery();
    }
}

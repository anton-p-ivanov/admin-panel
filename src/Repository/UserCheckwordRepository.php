<?php

namespace App\Repository;

use App\Entity\User\User;
use App\Entity\User\Checkword;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class UserCheckwordRepository
 *
 * @package App\Repository
 */
class UserCheckwordRepository extends ServiceEntityRepository
{
    /**
     * UserCheckwordRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Checkword::class);
    }

    /**
     * @param User $user
     *
     * @return mixed
     */
    public function expireUserCheckwords(User $user)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        return $queryBuilder->update('App:User\Checkword', 'c')
            ->set('c.isExpired', true)
            ->where('c.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }
}

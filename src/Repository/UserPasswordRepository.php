<?php

namespace App\Repository;

use App\Entity\User\User;
use App\Entity\User\Password;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class UserPasswordRepository
 * @package App\Repository
 */
class UserPasswordRepository extends ServiceEntityRepository
{
    /**
     * UserPasswordRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Password::class);
    }

    /**
     * @param User $user
     *
     * @return mixed
     */
    public function expireUserPasswords(User $user)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        return $queryBuilder->update('App:User\Password', 'c')
            ->set('c.isExpired', true)
            ->where('c.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }
}

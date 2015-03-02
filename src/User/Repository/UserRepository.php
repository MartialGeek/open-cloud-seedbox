<?php

namespace Martial\Warez\User\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Martial\Warez\User\Entity\User;

class UserRepository extends EntityRepository implements UserRepositoryInterface
{
    /**
     * Finds a user by its email and its password.
     *
     * @param string $email
     * @param string $password
     * @return User
     * @throws NoResultException
     */
    public function findUserByEmailAndPassword($email, $password)
    {
        return $this
            ->createQueryBuilder('u')
            ->where('u.email = :email')
            ->andWhere('u.password = :password')
            ->getQuery()
            ->setParameters([
                'email' => $email,
                'password' => $password
            ])
            ->getSingleResult();
    }
}

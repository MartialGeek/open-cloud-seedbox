<?php

namespace Martial\Warez\User\Repository;

use Doctrine\ORM\NoResultException;
use Martial\Warez\User\Entity\User;

interface UserRepositoryInterface
{
    /**
     * Finds a user by its email and its password.
     *
     * @param string $email
     * @param string $password
     * @return User
     * @throws NoResultException
     */
    public function findUserByEmailAndPassword($email, $password);
}

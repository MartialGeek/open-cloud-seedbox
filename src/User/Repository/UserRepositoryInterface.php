<?php

namespace Martial\Warez\User\Repository;

use Martial\Warez\User\Entity\User;

interface UserRepositoryInterface
{
    /**
     * Finds a user by its email and its password.
     *
     * @param string $email
     * @param string $password
     * @return User
     */
    public function findUserByEmailAndPassword($email, $password);
}

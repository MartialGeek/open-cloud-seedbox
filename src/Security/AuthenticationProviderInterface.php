<?php

namespace Martial\Warez\Security;

use Martial\Warez\User\Entity\User;

interface AuthenticationProviderInterface
{
    /**
     * Authenticates a user by its email and a non-hashed password or throw an exception.
     *
     * @param string $email
     * @param string $password
     * @return User
     * @throws BadCredentialsException
     */
    public function authenticateByEmail($email, $password);

    /**
     * Generates a hash from the given password.
     *
     * @param string $password
     * @return string
     */
    public function generatePasswordHash($password);
}

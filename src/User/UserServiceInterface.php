<?php

namespace Martial\Warez\User;

use Martial\Warez\User\Entity\User;

interface UserServiceInterface
{
    /**
     * Registers a new user.
     *
     * @param User $user
     */
    public function register(User $user);

    /**
     * @param User $user
     */
    public function unregister(User $user);

    /**
     * Updates the account of a user.
     *
     * @param User $user
     */
    public function updateAccount(User $user);

    /**
     * Authenticates a user with its email.
     *
     * @param string $email
     * @param string $password
     * @return User
     */
    public function authenticateByEmail($email, $password);
}

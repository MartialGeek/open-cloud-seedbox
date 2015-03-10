<?php

namespace Martial\Warez\User;

use Martial\Warez\Security\BadCredentialsException;
use Martial\Warez\User\Entity\Profile;
use Martial\Warez\User\Entity\User;

interface UserServiceInterface
{
    /**
     * Registers a new user.
     *
     * @param User $user
     * @throws EmailAlreadyExistsException
     * @throws UsernameAlreadyExistsException
     */
    public function register(User $user);

    /**
     * Unregisters a user.
     *
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
     * @throws BadCredentialsException
     * @throws UserNotFoundException
     */
    public function authenticateByEmail($email, $password);

    /**
     * Finds a user by its ID.
     *
     * @param int $userId
     * @return User
     * @throws UserNotFoundException
     */
    public function find($userId);

    /**
     * Updates the profile of a user.
     *
     * @param int $userId
     * @param Profile $profile
     * @return Profile
     */
    public function updateProfile($userId, Profile $profile);
}

<?php

namespace Martial\Warez\Security;

use Martial\Warez\User\Entity\User;

class AuthenticationProvider implements AuthenticationProviderInterface
{
    /**
     * @var PasswordHashInterface
     */
    private $passwordHash;

    /**
     * @param PasswordHashInterface $passwordHash
     */
    public function __construct(PasswordHashInterface $passwordHash)
    {
        $this->passwordHash = $passwordHash;
    }

    /**
     * Checks if a user provided valid credentials.
     *
     * @param User $user
     * @param string $clearPassword
     * @return bool
     */
    public function hasValidCredentials(User $user, $clearPassword)
    {
        return $this->passwordHash->isValid($clearPassword, $user->getPassword());
    }
}

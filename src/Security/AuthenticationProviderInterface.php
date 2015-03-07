<?php

namespace Martial\Warez\Security;

use Martial\Warez\User\Entity\User;

interface AuthenticationProviderInterface
{
    /**
     * Checks if a user provided valid credentials.
     *
     * @param User $user
     * @param string $clearPassword
     * @return bool
     */
    public function hasValidCredentials(User $user, $clearPassword);
}

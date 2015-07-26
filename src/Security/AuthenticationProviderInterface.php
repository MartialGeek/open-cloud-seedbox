<?php

namespace Martial\OpenCloudSeedbox\Security;

interface AuthenticationProviderInterface
{
    /**
     * Checks if a user provided valid credentials.
     *
     * @param string $encodedPassword
     * @param string $clearPassword
     * @return bool
     */
    public function hasValidCredentials($encodedPassword, $clearPassword);
}

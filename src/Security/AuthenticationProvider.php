<?php

namespace Martial\OpenCloudSeedbox\Security;

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
     * @param string $encodedPassword
     * @param string $clearPassword
     * @return bool
     */
    public function hasValidCredentials($encodedPassword, $clearPassword)
    {
        return $this->passwordHash->isValid($clearPassword, $encodedPassword);
    }
}

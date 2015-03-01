<?php

namespace Martial\Warez\Security;


interface AuthenticationProviderInterface
{
    public function authenticate();

    /**
     * @param string $password
     * @return string
     */
    public function generatePasswordHash($password);
}

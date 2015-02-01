<?php

namespace Martial\Warez\T411\Api;

use Martial\Warez\T411\Api\Authentication\AccountDisabledException;
use Martial\Warez\T411\Api\Authentication\AccountNotConfirmedException;
use Martial\Warez\T411\Api\Authentication\Token;
use Martial\Warez\T411\Api\Authentication\UserNotFoundException;
use Martial\Warez\T411\Api\Authentication\WrongPasswordException;

interface ClientInterface
{
    /**
     * Authenticates a user and returns an authentication token.
     *
     * @param string $username
     * @param string $password
     * @return Token
     * @throws UserNotFoundException
     * @throws AccountNotConfirmedException
     * @throws AccountDisabledException
     * @throws WrongPasswordException
     */
    public function authenticate($username, $password);
}

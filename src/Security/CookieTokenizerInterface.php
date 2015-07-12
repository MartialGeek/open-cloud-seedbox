<?php

namespace Martial\Warez\Security;

use Martial\Warez\User\Entity\User;

interface CookieTokenizerInterface
{
    /**
     * Generates and stores a token for the given user.
     *
     * @param User $user
     * @return CookieTokenInterface
     */
    public function generateAndStoreToken(User $user);

    /**
     * Retrieves the token or throws an exception.
     *
     * @param string $id
     * @return CookieTokenInterface
     * @throws CookieTokenNotFoundException
     */
    public function findToken($id);
}

<?php

namespace Martial\OpenCloudSeedbox\Security;

use Martial\OpenCloudSeedbox\User\Entity\User;

interface CookieTokenInterface
{
    /**
     * Returns the generated ID.
     *
     * @return string
     */
    public function getTokenId();

    /**
     * Returns the generated hash.
     *
     * @return string
     */
    public function getTokenHash();

    /**
     * Returns the instance of the User entity.
     *
     * @return User
     */
    public function getUser();
}

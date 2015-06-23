<?php

namespace Martial\Warez\Security;

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
}

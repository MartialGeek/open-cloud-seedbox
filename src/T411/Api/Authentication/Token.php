<?php

namespace Martial\Warez\T411\Api\Authentication;

interface Token
{
    /**
     * Sets the UID.
     *
     * @param string $uid
     */
    public function setUid($uid);

    /**
     * Retrieves the UID.
     *
     * @return string
     */
    public function getUid();

    /**
     * Sets the token.
     *
     * @param string $token
     */
    public function setToken($token);

    /**
     * Retrieves the token.
     *
     * @return string
     */
    public function getToken();
}

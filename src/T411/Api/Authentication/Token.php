<?php

namespace Martial\Warez\T411\Api\Authentication;


class Token implements TokenInterface
{
    /**
     * @var string
     */
    private $uid;

    /**
     * @var string
     */
    private $token;

    /**
     * Sets the UID.
     *
     * @param string $uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * Retrieves the UID.
     *
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Sets the token.
     *
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * Retrieves the token.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
}

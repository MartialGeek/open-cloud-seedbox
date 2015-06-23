<?php

namespace Martial\Warez\Security;

class CookieToken implements CookieTokenInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $hash;

    /**
     * @param string $id
     * @param string $hash
     */
    public function __construct($id, $hash)
    {
        $this->id = $id;
        $this->hash = $hash;
    }

    /**
     * Returns the generated ID.
     *
     * @return string
     */
    public function getTokenId()
    {
        return $this->id;
    }

    /**
     * Returns the generated hash.
     *
     * @return string
     */
    public function getTokenHash()
    {
        return $this->hash;
    }
}

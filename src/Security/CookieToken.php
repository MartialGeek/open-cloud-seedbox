<?php

namespace Martial\Warez\Security;

use Martial\Warez\User\Entity\User;

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
     * @var User
     */
    private $user;

    /**
     * @param string $id
     * @param string $hash
     * @param User $user
     */
    public function __construct($id, $hash, User $user)
    {
        $this->id = $id;
        $this->hash = $hash;
        $this->user = $user;
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

    /**
     * Returns the instance of the User entity.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}

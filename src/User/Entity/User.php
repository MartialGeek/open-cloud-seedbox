<?php

namespace Martial\Warez\User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Martial\Warez\Doctrine\TimestampableTrait;

/**
 * Class User
 * @package Martial\Warez\User
 * @Entity(repositoryClass="\Martial\Warez\User\Repository\UserRepository")
 * @Table(name="users")
 * @HasLifecycleCallbacks
 */
class User
{
    use TimestampableTrait;

    /**
     * @var int
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @Column(type="string", length=50, nullable=false, unique=true)
     */
    protected $username;

    /**
     * @var string
     * @Column(type="string", length=250, nullable=false, unique=true)
     */
    protected $email;

    /**
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $password;

    /**
     * @var Profile
     * @OneToOne(targetEntity="Profile", mappedBy="user")
     */
    protected $profile;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return User
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return Profile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @param Profile $profile
     * @return User
     */
    public function setProfile(Profile $profile)
    {
        $this->profile = $profile;

        return $this;
    }
}

<?php

namespace Martial\OpenCloudSeedbox\User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\ORM\Mapping\Table;
use Martial\OpenCloudSeedbox\Settings\Entity\FreeboxSettingsEntity;
use Martial\OpenCloudSeedbox\Settings\Entity\TrackerSettingsEntity;

/**
 * Class User
 * @package Martial\OpenCloudSeedbox\User
 * @Entity(repositoryClass="\Martial\OpenCloudSeedbox\User\Repository\UserRepository")
 * @Table(name="users")
 * @HasLifecycleCallbacks
 */
class User
{
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
     * @var string
     * @Column(type="string", length=255, name="cookie_token_id", unique=true, nullable=true)
     */
    protected $cookieTokenId;

    /**
     * @var string
     * @Column(type="string", length=255, name="cookie_token_hash", nullable=true)
     */
    protected $cookieTokenHash;

    /**
     * @var TrackerSettingsEntity
     * @OneToOne(targetEntity="Martial\OpenCloudSeedbox\Settings\Entity\TrackerSettingsEntity", mappedBy="user")
     */
    protected $trackerSettings;

    /**
     * @var FreeboxSettingsEntity
     * @OneToOne(targetEntity="Martial\OpenCloudSeedbox\Settings\Entity\FreeboxSettingsEntity", mappedBy="user")
     */
    protected $freeboxSettings;

    /**
     * @var \DateTime
     * @Column(type="datetime", name="created_at", nullable=false)
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @Column(type="datetime", name="updated_at", nullable=false)
     */
    protected $updatedAt;

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
     * @return string
     */
    public function getCookieTokenId()
    {
        return $this->cookieTokenId;
    }

    /**
     * @param string $cookieTokenId
     * @return User
     */
    public function setCookieTokenId($cookieTokenId)
    {
        $this->cookieTokenId = $cookieTokenId;

        return $this;
    }

    /**
     * @return string
     */
    public function getCookieTokenHash()
    {
        return $this->cookieTokenHash;
    }

    /**
     * @param string $cookieTokenHash
     * @return User
     */
    public function setCookieTokenHash($cookieTokenHash)
    {
        $this->cookieTokenHash = $cookieTokenHash;

        return $this;
    }

    /**
     * @return TrackerSettingsEntity
     */
    public function getTrackerSettings()
    {
        return $this->trackerSettings;
    }

    /**
     * @param TrackerSettingsEntity $trackerSettings
     * @return self
     */
    public function setTrackerSettings(TrackerSettingsEntity $trackerSettings)
    {
        $this->trackerSettings = $trackerSettings;

        return $this;
    }

    /**
     * @return FreeboxSettingsEntity
     */
    public function getFreeboxSettings()
    {
        return $this->freeboxSettings;
    }

    /**
     * @param FreeboxSettingsEntity $freeboxSettings
     * @return self
     */
    public function setFreeboxSettings($freeboxSettings)
    {
        $this->freeboxSettings = $freeboxSettings;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return User
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @return User
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @PrePersist
     */
    public function onCreate()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * @PreUpdate
     */
    public function onUpdate()
    {
        $this->updatedAt = new \DateTime();
    }
}

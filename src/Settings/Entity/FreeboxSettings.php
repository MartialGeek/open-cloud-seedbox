<?php

namespace Martial\Warez\Settings\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * Class FreeboxSettings
 * @package Martial\Warez\Settings\Entity
 * @Entity
 * @Table(name="settings_freebox")
 */
class FreeboxSettings
{
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @Column(type="string", length=255, name="transport_host")
     */
    private $transportHost;

    /**
     * @var string
     * @Column(type="string", length=255, name="transport_port")
     */
    private $transportPort;

    /**
     * @var string
     * @Column(type="string", length=255, name="app_id")
     */
    private $appId;

    /**
     * @var string
     * @Column(type="string", length=255, name="app_name")
     */
    private $appName;

    /**
     * @var string
     * @Column(type="string", length=255, name="app_version")
     */
    private $appVersion;

    /**
     * @var string
     * @Column(type="string", length=255, name="device_name")
     */
    private $deviceName;

    /**
     * @var string
     * @Column(type="string", length=255, name="app_token")
     */
    private $appToken;

    /**
     * @var int
     * @Column(type="smallint", name="track_id")
     */
    private $trackId;

    /**
     * @var string
     * @Column(type="string", length=255)
     */
    private $challenge;

    /**
     * @var int
     * @Column(type="integer")
     */
    private $userId;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTransportHost()
    {
        return $this->transportHost;
    }

    /**
     * @param mixed $transportHost
     * @return self
     */
    public function setTransportHost($transportHost)
    {
        $this->transportHost = $transportHost;

        return $this;
    }

    /**
     * @return string
     */
    public function getTransportPort()
    {
        return $this->transportPort;
    }

    /**
     * @param string $transportPort
     */
    public function setTransportPort($transportPort)
    {
        $this->transportPort = $transportPort;
    }

    /**
     * @return string
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @param string $appId
     * @return self
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;

        return $this;
    }

    /**
     * @return string
     */
    public function getAppName()
    {
        return $this->appName;
    }

    /**
     * @param string $appName
     * @return self
     */
    public function setAppName($appName)
    {
        $this->appName = $appName;

        return $this;
    }

    /**
     * @return string
     */
    public function getAppVersion()
    {
        return $this->appVersion;
    }

    /**
     * @param string $appVersion
     * @return self
     */
    public function setAppVersion($appVersion)
    {
        $this->appVersion = $appVersion;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeviceName()
    {
        return $this->deviceName;
    }

    /**
     * @param string $deviceName
     * @return self
     */
    public function setDeviceName($deviceName)
    {
        $this->deviceName = $deviceName;

        return $this;
    }

    /**
     * @return string
     */
    public function getAppToken()
    {
        return $this->appToken;
    }

    /**
     * @param string $appToken
     * @return self
     */
    public function setAppToken($appToken)
    {
        $this->appToken = $appToken;

        return $this;
    }

    /**
     * @return int
     */
    public function getTrackId()
    {
        return $this->trackId;
    }

    /**
     * @param int $trackId
     * @return self
     */
    public function setTrackId($trackId)
    {
        $this->trackId = $trackId;

        return $this;
    }

    /**
     * @return string
     */
    public function getChallenge()
    {
        return $this->challenge;
    }

    /**
     * @param string $challenge
     * @return self
     */
    public function setChallenge($challenge)
    {
        $this->challenge = $challenge;

        return $this;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     * @return self
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }
}

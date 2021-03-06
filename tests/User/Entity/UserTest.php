<?php

namespace Martial\OpenCloudSeedbox\Tests\User\Entity;

use Martial\OpenCloudSeedbox\Settings\Entity\FreeboxSettingsEntity;
use Martial\OpenCloudSeedbox\Settings\Entity\TrackerSettingsEntity;
use Martial\OpenCloudSeedbox\User\Entity\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    public function testEntity()
    {
        $id = 42;
        $username = 'johndoe';
        $email = 'john.doe@gmail.com';
        $password = password_hash(uniqid(), PASSWORD_BCRYPT);
        $cookieTokenId = uniqid();
        $cookieTokenHash = sha1($password);
        $trackerSettings = new TrackerSettingsEntity();
        $freeboxSettings = new FreeboxSettingsEntity();
        $createdAt = new \DateTime('-1 month');
        $updatedAt = new \DateTime();

        $user = new User();
        $user
            ->setId($id)
            ->setUsername($username)
            ->setEmail($email)
            ->setPassword($password)
            ->setCookieTokenId($cookieTokenId)
            ->setCookieTokenHash($cookieTokenHash)
            ->setTrackerSettings($trackerSettings)
            ->setFreeboxSettings($freeboxSettings)
            ->setCreatedAt($createdAt)
            ->setUpdatedAt($updatedAt);

        $this->assertSame($id, $user->getId());
        $this->assertSame($username, $user->getUsername());
        $this->assertSame($email, $user->getEmail());
        $this->assertSame($password, $user->getPassword());
        $this->assertSame($cookieTokenId, $user->getCookieTokenId());
        $this->assertSame($cookieTokenHash, $user->getCookieTokenHash());
        $this->assertSame($trackerSettings, $user->getTrackerSettings());
        $this->assertSame($freeboxSettings, $user->getFreeboxSettings());
        $this->assertSame($createdAt, $user->getCreatedAt());
        $this->assertSame($updatedAt, $user->getUpdatedAt());
    }

    public function testOnCreateHook()
    {
        $user = new User();

        $this->assertNull($user->getCreatedAt());
        $this->assertNull($user->getUpdatedAt());

        $user->onCreate();

        $this->assertInstanceOf('\DateTime', $user->getCreatedAt());
        $this->assertInstanceOf('\DateTime', $user->getUpdatedAt());
    }

    public function testOnUpdateHook()
    {
        $user = new User();

        $this->assertNull($user->getUpdatedAt());
        $user->onUpdate();
        $this->assertInstanceOf('\DateTime', $user->getUpdatedAt());
    }
}

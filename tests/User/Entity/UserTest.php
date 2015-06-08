<?php

namespace Martial\Warez\Tests\User\Entity;

use Martial\Warez\Settings\Entity\FreeboxSettingsEntity;
use Martial\Warez\Settings\Entity\TrackerSettingsEntity;
use Martial\Warez\User\Entity\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    public function testEntity()
    {
        $id = 42;
        $username = 'johndoe';
        $email = 'john.doe@gmail.com';
        $password = password_hash(uniqid(), PASSWORD_BCRYPT);
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
            ->setTrackerSettings($trackerSettings)
            ->setFreeboxSettings($freeboxSettings)
            ->setCreatedAt($createdAt)
            ->setUpdatedAt($updatedAt);

        $this->assertSame($id, $user->getId());
        $this->assertSame($username, $user->getUsername());
        $this->assertSame($email, $user->getEmail());
        $this->assertSame($password, $user->getPassword());
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

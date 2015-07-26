<?php

namespace Martial\OpenCloudSeedbox\Tests\Settings\Entity;

use Martial\OpenCloudSeedbox\Settings\Entity\TrackerSettingsEntity;
use Martial\OpenCloudSeedbox\User\Entity\User;

class TrackerSettingsEntityTest extends \PHPUnit_Framework_TestCase
{
    public function testEntity()
    {
        $id = 42;
        $password = password_hash('password', PASSWORD_BCRYPT);
        $username = 'joe';
        $user = new User();
        $createdAt = new \DateTime('-1 year');
        $updatedAt = new \DateTime();

        $settings = new TrackerSettingsEntity();
        $settings
            ->setId($id)
            ->setPassword($password)
            ->setUsername($username)
            ->setUser($user)
            ->setCreatedAt($createdAt)
            ->setUpdatedAt($updatedAt);

        $this->assertSame($id, $settings->getId());
        $this->assertSame($password, $settings->getPassword());
        $this->assertSame($username, $settings->getUsername());
        $this->assertSame($user, $settings->getUser());
        $this->assertSame($createdAt, $settings->getCreatedAt());
        $this->assertSame($updatedAt, $settings->getUpdatedAt());
    }

    public function testOnCreateHook()
    {
        $settings = new TrackerSettingsEntity();

        $this->assertNull($settings->getCreatedAt());
        $this->assertNull($settings->getUpdatedAt());

        $settings->onCreate();

        $this->assertInstanceOf('\DateTime', $settings->getCreatedAt());
        $this->assertInstanceOf('\DateTime', $settings->getUpdatedAt());
    }

    public function testOnUpdateHook()
    {
        $settings = new TrackerSettingsEntity();

        $this->assertNull($settings->getUpdatedAt());
        $settings->onUpdate();
        $this->assertInstanceOf('\DateTime', $settings->getUpdatedAt());
    }
}

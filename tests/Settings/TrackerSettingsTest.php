<?php

namespace Martial\Warez\Tests\Settings;

use Martial\Warez\Settings\TrackerSettings;

class TrackerSettingsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TrackerSettings
     */
    public $trackerSettings;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $encoder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $em;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $user;

    protected function setUp()
    {
        $this->encoder = $this->getMock('\Martial\Warez\Security\EncoderInterface');
        $this->em = $this->getMock('\Doctrine\ORM\EntityManagerInterface');
        $this->trackerSettings = new TrackerSettings($this->encoder, $this->em);

        $this->user = $this
            ->getMockBuilder('\Martial\Warez\User\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetExistingSettings()
    {
        $currentSettings = $this->getSettings();
        $result = $this->trackerSettings->getSettings($this->user);
        $this->assertSame($currentSettings, $result);
    }

    public function testGetEmptySettings()
    {
        $this->getSettings(false);
        $result = $this->trackerSettings->getSettings($this->user);
        $this->assertInstanceOf('\Martial\Warez\Settings\Entity\TrackerSettingsEntity', $result);
        $this->assertSame($this->user, $result->getUser());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getSettings($alreadyExists = true)
    {
        $repo = $this->getMock('\Doctrine\Common\Persistence\ObjectRepository');
        $clearPassword = $alreadyExists ? 'aP@ssW0rd' : null;
        $encodedPassword = $alreadyExists ? uniqid() : null;

        $currentSettings = $alreadyExists ? $this
            ->getMockBuilder('\Martial\Warez\Settings\Entity\TrackerSettingsEntity')
            ->disableOriginalConstructor()
            ->getMock() :
            null;

        $this
            ->em
            ->expects($this->once())
            ->method('getRepository')
            ->with('\Martial\Warez\Settings\Entity\TrackerSettingsEntity')
            ->willReturn($repo);

        $repo
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['user' => $this->user])
            ->willReturn($currentSettings);

        if ($alreadyExists) {
            $currentSettings
                ->expects($this->once())
                ->method('getPassword')
                ->willReturn($encodedPassword);

            $currentSettings
                ->expects($this->once())
                ->method('setPassword')
                ->with($clearPassword);
        }

        $this
            ->encoder
            ->expects($this->once())
            ->method('decode')
            ->with($encodedPassword)
            ->willReturn($clearPassword);

        return $currentSettings;
    }
}

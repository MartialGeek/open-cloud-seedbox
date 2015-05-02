<?php

namespace Martial\Warez\Tests\User;

use Martial\Warez\Settings\TrackerSettings;

class TrackerServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TrackerSettings
     */
    public $settings;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $entity;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $encoder;

    /**
     * @var string
     */
    public $trackerPassword;

    /**
     * @var string
     */
    public $encodedTrackerPassword;

    public function testTrackerPasswordEncoding()
    {
        $this
            ->entity
            ->expects($this->once())
            ->method('getPassword')
            ->will($this->returnValue($this->trackerPassword));

        $this
            ->entity
            ->expects($this->once())
            ->method('setPassword')
            ->with($this->equalTo($this->encodedTrackerPassword))
            ->will($this->returnValue($this->entity));

        $this
            ->encoder
            ->expects($this->once())
            ->method('encode')
            ->with($this->equalTo($this->trackerPassword))
            ->will($this->returnValue($this->encodedTrackerPassword));

        $this->settings->encodeTrackerPassword($this->entity);
    }

    public function testTrackerPasswordDecoding()
    {
        $this
            ->entity
            ->expects($this->once())
            ->method('getPassword')
            ->will($this->returnValue($this->encodedTrackerPassword));

        $this
            ->entity
            ->expects($this->once())
            ->method('setPassword')
            ->with($this->equalTo($this->trackerPassword))
            ->will($this->returnValue($this->entity));

        $this
            ->encoder
            ->expects($this->once())
            ->method('decode')
            ->with($this->equalTo($this->encodedTrackerPassword))
            ->will($this->returnValue($this->trackerPassword));

        $this->settings->decodeTrackerPassword($this->entity);
    }

    protected function setUp()
    {
        $this->entity = $this
            ->getMockBuilder('\Martial\Warez\Settings\Entity\TrackerSettingsEntity')
            ->disableOriginalConstructor()
            ->getMock();

        $this->encoder = $this->getMock('\Martial\Warez\Security\EncoderInterface');
        $this->settings = new TrackerSettings($this->encoder);
        $this->trackerPassword = sha1(uniqid());
        $this->encodedTrackerPassword = md5($this->trackerPassword);
    }
}

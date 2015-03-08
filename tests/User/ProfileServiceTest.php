<?php

namespace Martial\Warez\Tests\User;

use Martial\Warez\User\ProfileService;

class ProfileServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProfileService
     */
    public $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $profile;

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
            ->profile
            ->expects($this->once())
            ->method('getTrackerPassword')
            ->will($this->returnValue($this->trackerPassword));

        $this
            ->profile
            ->expects($this->once())
            ->method('setTrackerPassword')
            ->with($this->equalTo($this->encodedTrackerPassword))
            ->will($this->returnValue($this->profile));

        $this
            ->encoder
            ->expects($this->once())
            ->method('encode')
            ->with($this->equalTo($this->trackerPassword))
            ->will($this->returnValue($this->encodedTrackerPassword));

        $this->service->encodeTrackerPassword($this->profile);
    }

    public function testTrackerPasswordDecoding()
    {
        $this
            ->profile
            ->expects($this->once())
            ->method('getTrackerPassword')
            ->will($this->returnValue($this->encodedTrackerPassword));

        $this
            ->profile
            ->expects($this->once())
            ->method('setTrackerPassword')
            ->with($this->equalTo($this->trackerPassword))
            ->will($this->returnValue($this->profile));

        $this
            ->encoder
            ->expects($this->once())
            ->method('decode')
            ->with($this->equalTo($this->encodedTrackerPassword))
            ->will($this->returnValue($this->trackerPassword));

        $this->service->decodeTrackerPassword($this->profile);
    }

    protected function setUp()
    {
        $this->profile = $this
            ->getMockBuilder('\Martial\Warez\User\Entity\Profile')
            ->disableOriginalConstructor()
            ->getMock();

        $this->encoder = $this->getMock('\Martial\Warez\Security\EncoderInterface');
        $this->service = new ProfileService($this->encoder);
        $this->trackerPassword = sha1(uniqid());
        $this->encodedTrackerPassword = md5($this->trackerPassword);
    }
}

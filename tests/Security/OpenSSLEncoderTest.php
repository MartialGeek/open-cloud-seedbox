<?php

namespace Martial\OpenCloudSeedbox\Tests\Security;

use Martial\OpenCloudSeedbox\Security\OpenSSLEncoder;

class OpenSSLEncoderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $rsa;

    /**
     * @var OpenSSLEncoder
     */
    public $encoder;

    protected function setUp()
    {
        $this->rsa = $this->getMockBuilder('\Pikirasa\RSA')->disableOriginalConstructor()->getMock();
        $this->encoder = new OpenSSLEncoder($this->rsa);
    }
    public function testEncode()
    {
        $clearData = 'mYsUp3rP@ssw0rd';

        $this
            ->rsa
            ->expects($this->once())
            ->method('encrypt')
            ->with($clearData);

        $this->encoder->encode($clearData);
    }

    public function testDecode()
    {
        $encodedData = sha1(uniqid());

        $this
            ->rsa
            ->expects($this->once())
            ->method('decrypt')
            ->with($encodedData);

        $this->encoder->decode($encodedData);
    }
}

<?php

namespace Martial\OpenCloudSeedbox\Tests\Security;

use Martial\OpenCloudSeedbox\Security\OpenSSLEncoder;

class OpenSSLEncoderTest extends \PHPUnit_Framework_TestCase
{
    public function testEncoder()
    {
        $password = 'p@ssW0rd';
        $salt = '';
        $data = 'A raw string';
        $encoder = new OpenSSLEncoder($password, $salt);
        $encodedData = $encoder->encode($data);
        $this->assertNotSame($data, $encodedData);
        $this->assertSame($data, $encoder->decode($encodedData));
    }
}

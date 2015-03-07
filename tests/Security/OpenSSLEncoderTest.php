<?php

namespace Martial\Warez\Tests\Security;

use Martial\Warez\Security\OpenSSLEncoder;

class OpenSSLEncoderTest extends \PHPUnit_Framework_TestCase
{
    public function testEncoder()
    {
        $password = 'p@ssW0rd';
        $salt = substr(sha1(uniqid()), 0, 16);
        $data = 'A raw string';
        $encoder = new OpenSSLEncoder($password, $salt);
        $encodedData = $encoder->encode($data);
        $this->assertSame($data, $encoder->decode($encodedData));
    }
}

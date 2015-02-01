<?php

namespace Martial\Warez\Tests\T411\Api\Authentication;

use Martial\Warez\T411\Api\Authentication\Token;

class TokenTest extends \PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $uid = uniqid();
        $tokenString = uniqid();
        $token = new Token();
        $token->setUid($uid);
        $token->setToken($tokenString);

        $this->assertSame($uid, $token->getUid());
        $this->assertSame($tokenString, $token->getToken());
    }
}

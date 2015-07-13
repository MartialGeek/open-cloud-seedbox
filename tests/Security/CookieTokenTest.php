<?php

namespace Martial\Warez\Tests\Security;

use Martial\Warez\Security\CookieToken;

class CookieTokenTest extends \PHPUnit_Framework_TestCase
{
    public function testCookieToken()
    {
        $user = $this
            ->getMockBuilder('\Martial\Warez\User\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();

        $id = uniqid();
        $hash = sha1(uniqid(), true);
        $token = new CookieToken($id, $hash, $user);

        $this->assertSame($id, $token->getTokenId());
        $this->assertSame($hash, $token->getTokenHash());
        $this->assertSame($user, $token->getUser());
    }
}

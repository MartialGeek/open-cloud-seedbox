<?php

namespace Martial\Warez\Tests\Security;

use Martial\Warez\Security\CookieToken;

class CookieTokenTest extends \PHPUnit_Framework_TestCase
{
    public function testCookieToken()
    {
        $id = uniqid();
        $hash = sha1(uniqid(), true);
        $token = new CookieToken($id, $hash);

        $this->assertSame($id, $token->getTokenId());
        $this->assertSame($hash, $token->getTokenHash());
    }
}

<?php

namespace Martial\Warez\Tests\Security;


use Martial\Warez\Security\BlowfishHashPassword;

class BlowfishHashPasswordTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BlowfishHashPassword
     */
    public $hashPassword;

    public function testEncryption()
    {
        $clearPassword = 'aSupe3P@ss0rd';
        $hashedPassword = $this->hashPassword->generateHash($clearPassword);
        $this->assertTrue($this->hashPassword->isValid($clearPassword, $hashedPassword));
        $this->assertFalse($this->hashPassword->isValid('anotherP@ssw0rd', $hashedPassword));
    }

    protected function setUp()
    {
        $this->hashPassword = new BlowfishHashPassword();
    }
}

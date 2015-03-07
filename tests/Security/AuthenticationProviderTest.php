<?php

namespace Martial\Warez\Tests\Security;

use Martial\Warez\Security\AuthenticationProvider;
use Martial\Warez\User\Entity\User;

class AuthenticationProviderTest extends \PHPUnit_Framework_TestCase
{
    const BAD_CREDENTIALS = 'bad_credentials';

    /**
     * @var AuthenticationProvider
     */
    public $authenticationService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $passwordHash;

    /**
     * @var User
     */
    public $user;

    /**
     * @var string
     */
    public $clearPassword;

    public function testValidCredentialsShouldReturnTrue()
    {
        $this->hasValidCredentials();
    }

    public function testInvalidCredentialsShouldReturnFalse()
    {
        $this->hasValidCredentials([self::BAD_CREDENTIALS]);
    }

    protected function hasValidCredentials(array $options = [])
    {
        $isValidCredentials = !in_array(self::BAD_CREDENTIALS, $options);

        $this
            ->passwordHash
            ->expects($this->once())
            ->method('isValid')
            ->with($this->equalTo($this->clearPassword), $this->equalTo($this->user->getPassword()))
            ->will($this->returnValue($isValidCredentials));

        $result = $this->authenticationService->hasValidCredentials($this->user, $this->clearPassword);
        $this->assertSame($isValidCredentials, $result);
    }

    protected function setUp()
    {
        $this->passwordHash = $this->getMock('\Martial\Warez\Security\PasswordHashInterface');
        $this->clearPassword = 'aSuperP@ssw0rd';
        $this->user = new User();
        $this->user
            ->setId(12)
            ->setPassword('plop')
            ->setUsername('Toto')
            ->setPassword(password_hash($this->clearPassword, PASSWORD_BCRYPT))
            ->setCreatedAt(new \DateTime('-1 year'))
            ->setUpdatedAt(new \DateTime('-1 week'));

        $this->authenticationService = new AuthenticationProvider($this->passwordHash);
    }
}

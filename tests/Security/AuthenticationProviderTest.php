<?php

namespace Martial\Warez\Tests\Security;

use Doctrine\ORM\NoResultException;
use Martial\Warez\Security\AuthenticationProvider;
use Martial\Warez\Security\BadCredentialsException;
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
    public $em;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $repo;

    /**
     * @var User
     */
    public $user;

    /**
     * @var string
     */
    public $clearPassword;

    public function testAuthenticateUser()
    {
        $this->authenticate();
    }

    /**
     * @expectedException \Martial\Warez\User\UserNotFoundException
     */
    public function testAuthenticateUserWithBadCredentialsShouldThrowAnException()
    {
        $this->authenticate([self::BAD_CREDENTIALS]);
    }

    protected function authenticate(array $options = [])
    {
        $findResult = in_array(self::BAD_CREDENTIALS, $options) ?
            $this->throwException(new NoResultException()) :
            $this->returnValue($this->user);

        $this
            ->em
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('\Martial\Warez\User\Entity\User'))
            ->will($this->returnValue($this->repo));

        $this
            ->repo
            ->expects($this->once())
            ->method('findUserByEmail')
            ->with($this->equalTo($this->user->getEmail()))
            ->will($findResult);

        try {
            $user = $this->authenticationService->authenticateByEmail($this->user->getEmail(), $this->clearPassword);
            if (!in_array(self::BAD_CREDENTIALS, $options)) {
                $this->assertSame($this->user, $user);
            }
        } catch (BadCredentialsException $e) {

        }
    }

    protected function setUp()
    {
        $this->em = $this
            ->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->clearPassword = 'aSuperP@ssw0rd';
        $this->user = new User();
        $this->user
            ->setId(12)
            ->setPassword('plop')
            ->setUsername('Toto')
            ->setPassword(password_hash($this->clearPassword, PASSWORD_BCRYPT))
            ->setCreatedAt(new \DateTime('-1 year'))
            ->setUpdatedAt(new \DateTime('-1 week'));

        $this->repo = $this->getMock('\Martial\Warez\User\Repository\UserRepositoryInterface');

        $this->authenticationService = new AuthenticationProvider($this->em);
    }
}

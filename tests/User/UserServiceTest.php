<?php

namespace Martial\Warez\Tests\User;

use Martial\Warez\User\Entity\User;
use Martial\Warez\User\UserService;

class UserServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $authenticationProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $em;

    /**
     * @var UserService
     */
    public $userService;

    /**
     * @var User
     */
    public $userEntity;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $hashedPassword;

    public function testRegisterUser()
    {
        $this
            ->authenticationProvider
            ->expects($this->once())
            ->method('generatePasswordHash')
            ->with($this->equalTo($this->password))
            ->will($this->returnValue($this->hashedPassword));

        $this
            ->em
            ->expects($this->once())
            ->method('persist')
            ->with($this->equalTo($this->userEntity));

        $this
            ->em
            ->expects($this->once())
            ->method('flush');

        $this->userService->register($this->userEntity);
    }

    public function testUnregisterUser()
    {
        $this
            ->em
            ->expects($this->once())
            ->method('remove')
            ->with($this->equalTo($this->userEntity));

        $this
            ->em
            ->expects($this->once())
            ->method('flush');

        $this->userService->unregister($this->userEntity);
    }

    public function testUpdateUserAccount()
    {
        $this->userEntity->setId(1234);

        $this
            ->em
            ->expects($this->once())
            ->method('persist')
            ->with($this->equalTo($this->userEntity));

        $this
            ->em
            ->expects($this->once())
            ->method('flush');

        $this->userService->updateAccount($this->userEntity);
    }

    public function testAuthenticateUser()
    {
        $userRepository = $this->getMock('\Martial\Warez\User\Repository\UserRepositoryInterface');

        $this
            ->authenticationProvider
            ->expects($this->once())
            ->method('generatePasswordHash')
            ->with($this->equalTo($this->userEntity->getPassword()))
            ->will($this->returnValue($this->hashedPassword));

        $this
            ->em
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('\Martial\Warez\User\Entity\User'))
            ->will($this->returnValue($userRepository));

        $userRepository
            ->expects($this->once())
            ->method('findUserByEmailAndPassword')
            ->with($this->equalTo($this->userEntity->getEmail()), $this->equalTo($this->hashedPassword))
            ->will($this->returnValue($this->userEntity));

        $user = $this
            ->userService
            ->authenticateByEmail($this->userEntity->getEmail(), $this->userEntity->getPassword());

        $this->assertSame($user, $this->userEntity);
    }

    protected function setUp()
    {
        $this->authenticationProvider = $this->getMock('\Martial\Warez\Security\AuthenticationProviderInterface');

        $this->em = $this
            ->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->userService = new UserService($this->em, $this->authenticationProvider);

        $this->password = 'tot0Isth3Best';
        $this->hashedPassword = 'hashedpassword';

        $this->userEntity = new User();
        $this->userEntity->setUsername('Toto');
        $this->userEntity->setEmail('toto@gmail.com');
        $this->userEntity->setPassword($this->password);
    }
}

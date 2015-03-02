<?php

namespace Martial\Warez\Tests\User;

use Martial\Warez\Security\BadCredentialsException;
use Martial\Warez\User\Entity\User;
use Martial\Warez\User\UserService;

class UserServiceTest extends \PHPUnit_Framework_TestCase
{
    const EMAIL_ALREADY_EXISTS = 'email_already_exists';
    const USERNAME_ALREADY_EXISTS = 'username_already_exists';
    const BAD_CREDENTIALS = 'bad_credentials';
    const USER_NOT_FOUND = 'user_not_found';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $authenticationProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $em;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $repo;

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
        $this->register();
    }

    /**
     * @expectedException \Martial\Warez\User\EmailAlreadyExistsException
     */
    public function testRegisterUserShouldThrowAnExceptionWhenAnEmailIsAlreadyExist()
    {
        $this->register([self::EMAIL_ALREADY_EXISTS]);
    }

    /**
     * @expectedException \Martial\Warez\User\UsernameAlreadyExistsException
     */
    public function testRegisterUserShouldThrowAnExceptionWhenAnUsernameIsAlreadyExist()
    {
        $this->register([self::USERNAME_ALREADY_EXISTS]);
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
        $this->authenticate();
    }

    /**
     * @expectedException \Martial\Warez\Security\BadCredentialsException
     */
    public function testAuthenticateUserShouldThrowAnExceptionOnInvalidCredentials()
    {
        $this->authenticate([self::BAD_CREDENTIALS]);
    }

    public function testFindExistingUser()
    {
        $this->find();
    }

    /**
     * @expectedException \Martial\Warez\User\UserNotFoundException
     */
    public function testFindUserShouldThrowAnExceptionWhenAUserWasNotFound()
    {
        $this->find([self::USER_NOT_FOUND]);
    }

    protected function register(array $options = [])
    {
        $this->getRepository($this->any());

        $findByEmailInvocation = $this
            ->repo
            ->expects($this->any())
            ->method('findBy')
            ->withConsecutive(
                [$this->equalTo(['email' => $this->userEntity->getEmail()])],
                [$this->equalTo(['username' => $this->userEntity->getUsername()])]
            );

        if (in_array(self::EMAIL_ALREADY_EXISTS, $options)) {
            $findByEmailInvocation->will($this->onConsecutiveCalls(
                [$this->userEntity],
                [$this->userEntity]
            ));
        } elseif (in_array(self::USERNAME_ALREADY_EXISTS, $options)) {
            $findByEmailInvocation->will($this->onConsecutiveCalls(
                [],
                [$this->userEntity]
            ));
        } else {
            $findByEmailInvocation->will($this->returnValue([], []));

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
        }

        $this->userService->register($this->userEntity);
    }

    protected function authenticate(array $options = [])
    {
        $authenticationResult = in_array(self::BAD_CREDENTIALS, $options) ?
            $this->throwException(new BadCredentialsException()) :
            $this->returnValue($this->userEntity);

        $this
            ->authenticationProvider
            ->expects($this->once())
            ->method('authenticateByEmail')
            ->with($this->equalTo($this->userEntity->getEmail()), $this->equalTo($this->userEntity->getPassword()))
            ->will($authenticationResult);

        $user = $this
            ->userService
            ->authenticateByEmail($this->userEntity->getEmail(), $this->userEntity->getPassword());

        if (!in_array(self::BAD_CREDENTIALS, $options)) {
            $this->assertSame($user, $this->userEntity);
        }
    }

    protected function find(array $options = [])
    {
        $findResult = in_array(self::USER_NOT_FOUND, $options) ? null : $this->userEntity;

        $this->getRepository($this->once());

        $this
            ->repo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($this->userEntity->getId()))
            ->will($this->returnValue($findResult));

        $user = $this->userService->find($this->userEntity->getId());

        if (!in_array(self::USER_NOT_FOUND, $options)) {
            $this->assertSame($this->userEntity, $user);
        }
    }

    protected function getRepository(\PHPUnit_Framework_MockObject_Matcher_Invocation $numberOfCalls)
    {
        $this
            ->em
            ->expects($numberOfCalls)
            ->method('getRepository')
            ->with($this->equalTo('\Martial\Warez\User\Entity\User'))
            ->will($this->returnValue($this->repo));
    }

    protected function setUp()
    {
        $this->authenticationProvider = $this->getMock('\Martial\Warez\Security\AuthenticationProviderInterface');

        $this->em = $this
            ->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->repo = $this->getMock('\Martial\Warez\User\Repository\UserRepositoryInterface');

        $this->userService = new UserService($this->em, $this->authenticationProvider);

        $this->password = 'tot0Isth3Best';
        $this->hashedPassword = 'hashedpassword';

        $this->userEntity = new User();
        $this->userEntity->setUsername('Toto');
        $this->userEntity->setEmail('toto@gmail.com');
        $this->userEntity->setPassword($this->password);
    }
}

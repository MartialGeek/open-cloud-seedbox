<?php

namespace Martial\Warez\Tests\Front\Controller;

use Martial\Warez\Form\Login;
use Martial\Warez\Form\Profile;
use Martial\Warez\Front\Controller\UserController;
use Martial\Warez\Security\BadCredentialsException;
use Martial\Warez\User\UserNotFoundException;

class UserControllerTest extends ControllerTestCase
{
    const LOGIN_SUCCESS = 1;
    const LOGIN_FAILED = 2;
    const LOGIN_FORM_INVALID = 3;
    const EMAIL_NOT_FOUND = 4;
    const PROFILE_FORM_INVALID = 5;

    /**
     * @var UserController
     */
    public $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $userService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $profileService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $user;

    public function testLoginSuccess()
    {
        $this->login(self::LOGIN_SUCCESS);
    }

    public function testLoginFailed()
    {
        $this->login(self::LOGIN_FAILED);
    }

    public function testLoginFormInvalid()
    {
        $this->login(self::LOGIN_FORM_INVALID);
    }

    public function testLoginEmailNotFound()
    {
        $this->login(self::EMAIL_NOT_FOUND);
    }

    public function testLogout()
    {
        $this->sessionSet([
            'connected' => false
        ]);
        $this->addFlash('notice', 'You are logged out.');
        $this->generateUrl('homepage', '/');
        $this->controller->logout();
    }

    public function testDisplayProfile()
    {
        $profile = $this
            ->getMockBuilder('\Martial\Warez\User\Entity\Profile')
            ->disableOriginalConstructor()
            ->getMock();

        $userId = 123;

        $this->sessionGet(['user_id' => $userId]);

        $this
            ->userService
            ->expects($this->once())
            ->method('getProfile')
            ->with($this->equalTo($userId))
            ->will($this->returnValue($profile));

        $profile
            ->expects($this->once())
            ->method('setTrackerPassword')
            ->with($this->equalTo(null))
            ->will($this->returnValue($this->user));

        $this->createForm(new Profile(), $profile);
        $this->createFormView();
        $this->render('@user/profile.html.twig', [
            'formProfile' => $this->formView
        ]);

        $this->controller->profile();
    }

    public function testUpdateProfile()
    {
        $this->updateProfile();
    }

    public function testUpdateInvalidProfile()
    {
        $this->updateProfile([self::PROFILE_FORM_INVALID]);
    }

    /**
     * Returns the full qualified class name of the controller you want to test.
     *
     * @return string
     */
    protected function getControllerClassName()
    {
        return '\Martial\Warez\Front\Controller\UserController';
    }

    /**
     * @param int $context
     */
    protected function login($context)
    {
        $loginParameters = [
            'email' => 'toto@gmail.com',
            'password' => 'p@ssW0rd'
        ];

        $username = 'Toto';

        $user = $this
            ->getMockBuilder('\Martial\Warez\User\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();

        $this->createForm(new Login());
        $this->handleRequest($this->request);

        switch ($context) {
            case self::LOGIN_SUCCESS:
                $this->formIsValid();
                $user
                    ->expects($this->once())
                    ->method('getUsername')
                    ->will($this->returnValue($username));
                $user
                    ->expects($this->once())
                    ->method('getId')
                    ->will($this->returnValue(123));
                $this->sessionSet([
                    'connected' => true,
                    'username' => $username,
                    'user_id' => 123
                ]);
                $this->generateUrl('homepage', '/');
                $this->authenticateByEmail($loginParameters, $this->returnValue($user));
                break;
            case self::LOGIN_FAILED:
                $this->formIsValid();
                $this->createFormView();
                $this->addFlash('error', 'You have provided a wrong password.');
                $this->render('@home/index.html.twig', ['loginForm' => $this->formView]);
                $this->authenticateByEmail($loginParameters, $this->throwException(new BadCredentialsException()));
                break;
            case self::EMAIL_NOT_FOUND:
                $this->formIsValid();
                $this->createFormView();
                $this->addFlash('error', 'This email was not found.');
                $this->render('@home/index.html.twig', ['loginForm' => $this->formView]);
                $this->authenticateByEmail($loginParameters, $this->throwException(new UserNotFoundException()));
                break;
            case self::LOGIN_FORM_INVALID:
                $this->formIsNotValid();
                $this->createFormView();
                $this->render('@home/index.html.twig', ['loginForm' => $this->formView]);
                break;
            default:
                throw new \InvalidArgumentException('Undefined context option');
        }

        $this->controller->login($this->request);
    }

    protected function updateProfile(array $options = [])
    {
        $this->createForm(new Profile());
        $this->handleRequest($this->request);

        if (in_array(self::PROFILE_FORM_INVALID, $options)) {
            $this->formIsNotValid();
            $this->createFormView();
            $this->render('@user/profile.html.twig', [
                'formProfile' => $this->formView
            ]);
        } else {
            $this->formIsValid();
            $userId = 123;
            $this->sessionGet(['user_id' => $userId]);
            $this->sessionRemove(['api_token']);

            $profile = $this
                ->getMockBuilder('\Martial\Warez\User\Entity\Profile')
                ->disableOriginalConstructor()
                ->getMock();

            $this
                ->form
                ->expects($this->once())
                ->method('getData')
                ->will($this->returnValue($profile));

            $this
                ->userService
                ->expects($this->once())
                ->method('updateProfile')
                ->with($this->equalTo($userId), $this->equalTo($profile));

            $this->addFlash('success', 'Profile successfully updated.');
            $this->generateUrl('user_profile', '/user/profile');
        }

        $this->controller->profileUpdate($this->request);
    }

    protected function defineDependencies()
    {
        $dependencies = parent::defineDependencies();
        $dependencies[] = $this->userService;
        $dependencies[] = $this->profileService;

        return $dependencies;
    }

    protected function authenticateByEmail(array $loginParameters, \PHPUnit_Framework_MockObject_Stub $returnValue)
    {
        $this->getRequestParameter('login', $loginParameters);
        $this
            ->userService
            ->expects($this->once())
            ->method('authenticateByEmail')
            ->with($this->equalTo($loginParameters['email']), $this->equalTo($loginParameters['password']))
            ->will($returnValue);
    }

    protected function setUp()
    {
        $this->userService = $this->getMock('\Martial\Warez\User\UserServiceInterface');
        $this->profileService = $this->getMock('\Martial\Warez\User\ProfileServiceInterface');

        $this->user = $this
            ->getMockBuilder('\Martial\Warez\User\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();

        parent::setUp();
    }
}

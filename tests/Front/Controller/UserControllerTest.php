<?php

namespace Martial\Warez\Tests\Front\Controller;

use Martial\Warez\Form\Login;
use Martial\Warez\Front\Controller\UserController;
use Martial\Warez\Security\BadCredentialsException;
use Martial\Warez\User\UserNotFoundException;

class UserControllerTest extends ControllerTestCase
{
    const LOGIN_SUCCESS = 1;
    const LOGIN_FAILED = 2;
    const LOGIN_FORM_INVALID = 3;
    const EMAIL_NOT_FOUND = 4;

    /**
     * @var UserController
     */
    public $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $userService;

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
                $this->sessionSet([
                    'connected' => true,
                    'username' => $username
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

    protected function defineDependencies()
    {
        $dependencies = parent::defineDependencies();
        $dependencies[] = $this->userService;

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
        parent::setUp();
    }
}

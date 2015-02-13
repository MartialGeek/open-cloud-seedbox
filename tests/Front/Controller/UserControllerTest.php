<?php

namespace Martial\Warez\Tests\Front\Controller;

use Martial\Warez\Form\Login;
use Martial\Warez\Front\Controller\UserController;

class UserControllerTest extends ControllerTestCase
{
    const LOGIN_SUCCESS = 1;
    const LOGIN_FAILED = 2;

    /**
     * @var UserController
     */
    public $controller;

    public function testLoginSuccess()
    {
        $this->login(self::LOGIN_SUCCESS);
    }

    public function testLoginFailed()
    {
        $this->login(self::LOGIN_FAILED);
    }

    public function testLogout()
    {
        $this->sessionSet('connected', false);
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
        $this->createForm(new Login());
        $this->handleRequest($this->request);

        if ($context === self::LOGIN_SUCCESS) {
            $this->formIsValid();
            $this->sessionSet('connected', true);
            $this->addFlash('notice', 'You are logged in.');
            $this->generateUrl('homepage', '/');
        } elseif ($context === self::LOGIN_FAILED) {
            $this->formIsNotValid();
            $this->createFormView();
            $this->render('@home/index.html.twig', ['loginForm' => $this->formView]);
        }

        $this->controller->login($this->request);
    }
}

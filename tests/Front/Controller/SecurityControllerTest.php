<?php

namespace Martial\Warez\Tests\Front\Controller;

use Martial\Warez\Form\Login;
use Martial\Warez\Front\Controller\SecurityController;

class SecurityControllerTest extends ControllerTestCase
{
    /**
     * @var SecurityController
     */
    public $controller;

    public function testLoginForm()
    {
        $this->createForm(new Login());
        $this->createFormView();
        $this->render('@security/loginForm.html.twig', [
            'loginForm' => $this->formView
        ]);

        $this->controller->loginForm();
    }

    /**
     * Returns the full qualified class name of the controller you want to test.
     *
     * @return string
     */
    protected function getControllerClassName()
    {
        return '\Martial\Warez\Front\Controller\SecurityController';
    }
}

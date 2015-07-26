<?php

namespace Martial\OpenCloudSeedbox\Tests\Front\Controller;

use Martial\OpenCloudSeedbox\Form\Login;
use Martial\OpenCloudSeedbox\Front\Controller\SecurityController;

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
        return '\Martial\OpenCloudSeedbox\Front\Controller\SecurityController';
    }
}

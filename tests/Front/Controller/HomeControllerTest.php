<?php

namespace Martial\Warez\Tests\Front\Controller;

use Martial\Warez\Form\Login;
use Martial\Warez\Front\Controller\HomeController;

class HomeControllerTest extends ControllerTestCase
{
    /**
     * @var HomeController
     */
    public $controller;

    public function testIndex()
    {
        $templatePath = '@home/index.html.twig';
        $this->createForm(new Login());
        $this->createFormView();
        $this->render($templatePath, ['loginForm' => $this->formView]);

        $response = $this->controller->index();
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
    }

    /**
     * Returns the full qualified class name of the controller you want to test.
     *
     * @return string
     */
    protected function getControllerClassName()
    {
        return '\Martial\Warez\Front\Controller\HomeController';
    }
}

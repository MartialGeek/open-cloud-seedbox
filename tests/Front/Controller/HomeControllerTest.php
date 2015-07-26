<?php

namespace Martial\OpenCloudSeedbox\Tests\Front\Controller;

use Martial\OpenCloudSeedbox\Form\Login;
use Martial\OpenCloudSeedbox\Front\Controller\HomeController;

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
        return '\Martial\OpenCloudSeedbox\Front\Controller\HomeController';
    }
}

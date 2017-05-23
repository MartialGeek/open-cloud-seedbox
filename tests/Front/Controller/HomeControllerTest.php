<?php

namespace Martial\OpenCloudSeedbox\Tests\Front\Controller;

use Martial\OpenCloudSeedbox\Form\Login;
use Martial\OpenCloudSeedbox\Front\Controller\HomeController;
use Symfony\Component\HttpFoundation\Response;

class HomeControllerTest extends ControllerTestCase
{
    /**
     * @var HomeController
     */
    public $controller;

    public function testIndex()
    {
        $templatePath = '@home/index.html.twig';
        $this->createForm(Login::class);
        $this->createFormView();
        $this->render($templatePath, ['loginForm' => $this->formView]);

        $response = $this->controller->index();
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * Returns the full qualified class name of the controller you want to test.
     *
     * @return string
     */
    protected function getControllerClassName()
    {
        return HomeController::class;
    }
}

<?php

namespace Martial\Warez\Tests\Front\Controller;

use Martial\Warez\Front\Controller\HomeController;

class HomeControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HomeController
     */
    public $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $twig;

    public function testIndex()
    {
        $templatePath = '@home/index.html.twig';

        $this
            ->twig
            ->expects($this->once())
            ->method('render')
            ->with($this->equalTo($templatePath));

        $response = $this->controller->index();
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
    }

    protected function setUp()
    {
        $this->twig = $this
            ->getMockBuilder('\Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();
        $this->controller = new HomeController($this->twig);
    }
}

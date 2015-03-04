<?php

namespace Martial\Warez\Tests\Front\Controller;

use Martial\Warez\Front\Controller\AbstractController;
use Symfony\Component\Form\FormTypeInterface;

abstract class ControllerTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $twig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $formFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $session;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $urlGenerator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $form;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $formView;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $flashBag;

    /**
     * @var AbstractController
     */
    public $controller;

    protected function setUp()
    {
        $this->twig = $this
            ->getMockBuilder('\Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();
        $this->formFactory = $this->getMock('\Symfony\Component\Form\FormFactoryInterface');
        $this->session = $this
            ->getMockBuilder('\Symfony\Component\HttpFoundation\Session\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlGenerator = $this->getMock('\Symfony\Component\Routing\Generator\UrlGeneratorInterface');
        $this->form = $this->getMock('\Symfony\Component\Form\FormInterface');
        $this->formView = $this->getMock('\Symfony\Component\Form\FormView');
        $this->request = $this
            ->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->request = $this
            ->getMockBuilder('\Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->query = $this
            ->getMockBuilder('\Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $this->flashBag = $this->getMock('\Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface');
        $this->createController();
    }

    protected function defineDependencies()
    {
        return [
            $this->twig,
            $this->formFactory,
            $this->session,
            $this->urlGenerator
        ];
    }

    protected function createController()
    {
        $dependencies = $this->defineDependencies();
        $reflectionClass = new \ReflectionClass($this->getControllerClassName());
        $this->controller = $reflectionClass->newInstanceArgs($dependencies);
    }

    /**
     * Returns the full qualified class name of the controller you want to test.
     *
     * @return string
     */
    abstract protected function getControllerClassName();

    /**
     * Simulates a call to the method create of the form factory component.
     *
     * @param FormTypeInterface $formType
     */
    protected function createForm(FormTypeInterface $formType)
    {
        $this
            ->formFactory
            ->expects($this->once())
            ->method('create')
            ->with($this->equalTo($formType))
            ->will($this->returnValue($this->form));
    }

    /**
     * Simulates a call to the createView method of the form component.
     */
    protected function createFormView()
    {
        $this
            ->form
            ->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($this->formView));
    }

    /**
     * Simulates a call to the handleRequest method of the form component.
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $request
     */
    protected function handleRequest(\PHPUnit_Framework_MockObject_MockObject $request)
    {
        $this
            ->form
            ->expects($this->once())
            ->method('handleRequest')
            ->with($this->equalTo($request))
            ->will($this->returnValue($this->form));
    }

    /**
     * Simulates a successful validation of the current form.
     */
    protected function formIsValid()
    {
        $this->setFormStatus(true);
    }

    /**
     * Simulates a failure in the validation of the current form.
     */
    protected function formIsNotValid()
    {
        $this->setFormStatus(false);
    }

    /**
     * Simulates a call to the render method of the Twig component.
     *
     * @param string $templatePath  The template path.
     * @param array $viewArgs       The parameters passed to the view.
     */
    protected function render($templatePath, array $viewArgs = [])
    {
        $args = [$this->equalTo($templatePath)];

        if (!empty($viewArgs)) {
            foreach ($viewArgs as $key => $value) {
                $args[] = $this->equalTo([$key => $value]);
            }
        }

        $invocation = $this
            ->twig
            ->expects($this->once())
            ->method('render');

        $invocation->getMatcher()->invocationMatcher = new \PHPUnit_Framework_MockObject_Matcher_Parameters($args);
    }

    /**
     * Simulates an insert in the session.
     *
     * @param array $keysAndValues
     */
    protected function sessionSet(array $keysAndValues)
    {
        $params = [];

        foreach ($keysAndValues as $key => $value) {
            $params[] = [$this->equalTo($key), $this->equalTo($value)];
        }

        $invocation = $this
            ->session
            ->expects($this->exactly(count($keysAndValues)))
            ->method('set');

        $invocation
            ->getMatcher()
            ->parametersMatcher = new \PHPUnit_Framework_MockObject_Matcher_ConsecutiveParameters($params);
    }

    /**
     * Simulates a new flash message.
     *
     * @param string $type
     * @param string $message
     */
    protected function addFlash($type, $message)
    {
        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('getFlashBag')
            ->will($this->returnValue($this->flashBag));

        $this
            ->flashBag
            ->expects($this->atLeastOnce())
            ->method('add')
            ->with($this->equalTo($type), $this->equalTo($message));
    }

    /**
     * Simulates a call to the generate method of the url generator.
     *
     * @param string $route
     * @param string $targetUrl
     */
    protected function generateUrl($route, $targetUrl)
    {
        $this
            ->urlGenerator
            ->expects($this->atLeastOnce())
            ->method('generate')
            ->with($this->equalTo($route))
            ->will($this->returnValue($targetUrl));
    }

    /**
     * Simulates a value retrieved via the request parameters bag of the Request component.
     * @param $param
     * @param $result
     * @param null $default
     */
    protected function getRequestParameter($param, $result, $default = null)
    {
        $this
            ->request
            ->request
            ->expects($this->any())
            ->method('get')
            ->with($this->equalTo($param), $this->equalTo($default))
            ->will($this->returnValue($result));
    }

    /**
     * Simulates a value retrieved via the request parameters bag of the Request component.
     * @param $param
     * @param $result
     * @param null $default
     */
    protected function getQueryParameter($param, $result, $default = null)
    {
        $this
            ->request
            ->query
            ->expects($this->any())
            ->method('get')
            ->with($this->equalTo($param), $this->equalTo($default))
            ->will($this->returnValue($result));
    }

    /**
     * Sets the status of the form.
     *
     * @param bool $valid
     */
    private function setFormStatus($valid = true)
    {
        $this
            ->form
            ->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue($valid));
    }
}

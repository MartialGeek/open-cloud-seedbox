<?php

namespace Martial\OpenCloudSeedbox\Tests\Front\Controller;

use Martial\OpenCloudSeedbox\Front\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

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
     * @var \PHPUnit_Framework_MockObject_MockObject|Request
     */
    public $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $requestParameterBag;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $queryParameterBag;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $flashBag;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $userService;

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
        $this->requestParameterBag = $this
            ->getMockBuilder('\Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryParameterBag = $this
            ->getMockBuilder('\Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->request = $this->requestParameterBag;
        $this->request->query = $this->queryParameterBag;
        $this->flashBag = $this->getMock('\Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface');
        $this->userService = $this->getMock('\Martial\OpenCloudSeedbox\User\UserServiceInterface');
        $this->createController();
    }

    protected function defineDependencies()
    {
        return [
            $this->twig,
            $this->formFactory,
            $this->session,
            $this->urlGenerator,
            $this->userService
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
     * @param string $formClass
     * @param mixed $data
     * @param array $options
     */
    protected function createForm($formClass, $data = null, $options = [])
    {
        $this
            ->formFactory
            ->expects($this->once())
            ->method('create')
            ->with($this->equalTo($formClass), $this->equalTo($data), $this->equalTo($options))
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
            $args[] = $this->equalTo($viewArgs);
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
        $this->session('set', $keysAndValues);
    }

    /**
     * Simulates a data retrieving from the session.
     *
     * @param array $keysAndReturnedValues
     */
    protected function sessionGet(array $keysAndReturnedValues)
    {
        $this->session('get', $keysAndReturnedValues);
    }

    /**
     * Simulates a removing from the session.
     *
     * @param array $keys
     */
    protected function sessionRemove(array $keys)
    {
        $this->session('remove', $keys);
    }

    /**
     * Simulates a call to know if a key is defined in the session.
     *
     * @param array $keysAndReturnedValues
     */
    protected function sessionHas(array $keysAndReturnedValues)
    {
        $this->session('has', $keysAndReturnedValues);
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
     *
     * @param $param
     * @param $result
     * @param null $default
     */
    protected function getRequestParameter($param, $result, $default = null)
    {
        $this
            ->requestParameterBag
            ->expects($this->any())
            ->method('get')
            ->with($this->equalTo($param), $this->equalTo($default))
            ->will($this->returnValue($result));
    }

    /**
     * Simulates a value retrieved via the request parameters bag of the Request component.
     *
     * @param $param
     * @param $result
     * @param null $default
     */
    protected function getQueryParameter($param, $result, $default = null)
    {
        $this
            ->queryParameterBag
            ->expects($this->any())
            ->method('get')
            ->with($this->equalTo($param), $this->equalTo($default))
            ->will($this->returnValue($result));
    }

    /**
     * Simulates a call to know if a query parameter is defined in the request.
     *
     * @param string $param
     * @param bool $isDefined
     */
    protected function hasQueryParameter($param, $isDefined = true)
    {
        $this
            ->queryParameterBag
            ->expects($this->any())
            ->method('has')
            ->with($this->equalTo($param))
            ->will($this->returnValue($isDefined));
    }

    /**
     * Simulates a call to the method find of the user service.
     *
     * @param int $userId
     * @param mixed $returnedValue
     */
    protected function getUser($userId, $returnedValue)
    {
        $this
            ->userService
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($userId))
            ->willReturn($returnedValue);
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

    /**
     * Drives the session behavior.
     *
     * @param string $action
     * @param array $keysAndValues
     */
    private function session($action, array $keysAndValues)
    {
        $supportedActions = ['get', 'set', 'remove', 'has'];

        if (!in_array($action, $supportedActions)) {
            throw new \InvalidArgumentException('Unsupported session action "' . $action . '"');
        }

        $params = [];
        $returnedValues = [];

        foreach ($keysAndValues as $key => $value) {
            if ('set' == $action) {
                $params[] = [$this->equalTo($key), $this->equalTo($value)];
            } elseif ('remove' == $action) {
                $params[] = [$this->equalTo($value)];
            } else {
                $params[] = [$this->equalTo($key)];
                $returnedValues[] = $value;
            }
        }

        $invocation = $this
            ->session
            ->expects($this->exactly(count($keysAndValues)))
            ->method($action);

        $invocation
            ->getMatcher()
            ->parametersMatcher = new \PHPUnit_Framework_MockObject_Matcher_ConsecutiveParameters($params);

        if ('get' == $action || 'has' == $action) {
            $invocation->will(new \PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls($returnedValues));
        }
    }
}

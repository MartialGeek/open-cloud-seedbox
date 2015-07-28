<?php

namespace Martial\OpenCloudSeedbox\Application;

use Martial\OpenCloudSeedbox\Front\Controller\ErrorControllerInterface;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionHandler implements EventSubscriberInterface
{
    /**
     * @var ErrorControllerInterface
     */
    private $controller;

    /**
     * @var bool
     */
    private $env;

    /**
     * @param ErrorControllerInterface $controller
     * @param bool $debug
     */
    public function __construct(ErrorControllerInterface $controller, $debug = false)
    {
        $this->controller = $controller;
        $this->env = $debug;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return [KernelEvents::EXCEPTION => 'onException'];
    }

    /**
     * @param GetResponseForExceptionEvent $e
     */
    public function onException(GetResponseForExceptionEvent $e)
    {
        if ($this->env == 'dev') {
            return;
        }

        $flattenException = FlattenException::create($e->getException());

        if ($flattenException->getStatusCode() == 404) {
            $e->setResponse($this->controller->resourceNotFound());
        } else {
            $e->setResponse($this->controller->internalError());
        }
    }
}

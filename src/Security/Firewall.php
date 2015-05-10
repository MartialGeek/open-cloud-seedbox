<?php

namespace Martial\Warez\Security;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class Firewall implements FirewallInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $requestUri = $event->getRequest()->getRequestUri();

        if (
            '/' != $requestUri &&
            '/login' != $requestUri &&
            'logout' != $requestUri &&
            '/form-login' != $requestUri &&
            '/freebox/import-settings' != $requestUri &&
            !preg_match('#^/upload#', $requestUri)
        ) {
            if (!$this->session->get('connected', false)) {
                $event->setResponse(new Response('Authentication required.', 401));
            }
        }
    }
}

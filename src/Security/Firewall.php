<?php

namespace Martial\Warez\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Firewall implements FirewallInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @param Session $session
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(Session $session, UrlGeneratorInterface $urlGenerator)
    {
        $this->session = $session;
        $this->urlGenerator = $urlGenerator;
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
                $this->session->getFlashBag()->add('error', 'You must open a session.');

                $event->setResponse(
                    new RedirectResponse($this->urlGenerator->generate('homepage'))
                );
            }
        }
    }
}

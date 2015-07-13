<?php

namespace Martial\Warez\Security;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RememberMeListener implements KernelRequestListenerInterface
{
    /**
     * @var CookieTokenizerInterface
     */
    private $cookieTokenizer;

    /**
     * @var Session
     */
    private $session;

    /**
     * @param CookieTokenizerInterface $cookieTokenizer
     * @param Session $session
     */
    public function __construct(CookieTokenizerInterface $cookieTokenizer, Session $session)
    {
        $this->cookieTokenizer = $cookieTokenizer;
        $this->session = $session;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $cookieBag = $event->getRequest()->cookies;

        if ($cookieBag->has('remember_me')) {
            $cookie = json_decode($cookieBag->get('remember_me'), true);

            try {
                $token = $this->cookieTokenizer->findToken($cookie['id']);
            } catch (CookieTokenNotFoundException $e) {
                $this->session->set('connected', false);
                return;
            }

            if ($token->getTokenHash() != $cookie['token']) {
                $this->session->set('connected', false);
            } else {
                $user = $token->getUser();
                $this->session->set('connected', true);
                $this->session->set('username', $user->getUsername());
                $this->session->set('user_id', $user->getId());
            }
        }
    }
}

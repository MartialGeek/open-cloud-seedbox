<?php

namespace Martial\Warez\Security;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

interface FirewallInterface
{
    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event);
}

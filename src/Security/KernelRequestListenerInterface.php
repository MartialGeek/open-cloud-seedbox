<?php

namespace Martial\OpenCloudSeedbox\Security;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

interface KernelRequestListenerInterface
{
    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event);
}

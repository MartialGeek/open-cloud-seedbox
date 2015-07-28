<?php

namespace Martial\OpenCloudSeedbox\Front\Controller;

use Symfony\Component\HttpFoundation\Response;

interface ErrorControllerInterface
{
    /**
     * This action is called when an exception is caught by the kernel.
     *
     * @return Response
     */
    public function internalError();

    /**
     * This action is called when a user requests a non existing resource.
     *
     * @return Response
     */
    public function resourceNotFound();
}

<?php

namespace Martial\OpenCloudSeedbox\Front\Controller;

use Symfony\Component\HttpFoundation\Response;

class ErrorController extends AbstractController implements ErrorControllerInterface
{
    /**
     * This action is called when an exception is caught by the kernel.
     *
     * @return Response
     */
    public function internalError()
    {
        return new Response($this->twig->render('@error/500.html.twig'), 500);
    }

    /**
     * This action is called when a user requests a non existing resource.
     *
     * @return Response
     */
    public function resourceNotFound()
    {
        return new Response($this->twig->render('@error/404.html.twig'), 404);
    }
}

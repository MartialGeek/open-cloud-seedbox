<?php

namespace Martial\Warez\Front\Controller;

use Symfony\Component\HttpFoundation\Response;

class HomeController
{
    /**
     * @var \Twig_Environment
     */
    private $twig;

    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @return Response
     */
    public function index()
    {
        return new Response(
            $this->twig->render('@home/index.html.twig')
        );
    }
}

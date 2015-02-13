<?php

namespace Martial\Warez\Front\Controller;

use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractController
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var UrlGenerator
     */
    protected $urlGenerator;

    /**
     * @param \Twig_Environment $twig
     * @param FormFactoryInterface $formFactory
     * @param Session $session
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        \Twig_Environment $twig,
        FormFactoryInterface $formFactory,
        Session $session,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->session = $session;
        $this->urlGenerator = $urlGenerator;
    }
}

<?php

namespace Martial\OpenCloudSeedbox\Front\Controller;

use Martial\OpenCloudSeedbox\User\Entity\User;
use Martial\OpenCloudSeedbox\User\UserServiceInterface;
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
     * @var UserServiceInterface
     */
    protected $userService;

    /**
     * @var User
     */
    private $currentUser;

    /**
     * @param \Twig_Environment $twig
     * @param FormFactoryInterface $formFactory
     * @param Session $session
     * @param UrlGeneratorInterface $urlGenerator
     * @param UserServiceInterface $userService
     */
    public function __construct(
        \Twig_Environment $twig,
        FormFactoryInterface $formFactory,
        Session $session,
        UrlGeneratorInterface $urlGenerator,
        UserServiceInterface $userService
    ) {
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->session = $session;
        $this->urlGenerator = $urlGenerator;
        $this->userService = $userService;
    }

    /**
     * @return User
     */
    protected function getUser()
    {
        if (is_null($this->currentUser)) {
            $this->currentUser = $this->userService->find($this->session->get('user_id'));
        }

        return $this->currentUser;
    }
}

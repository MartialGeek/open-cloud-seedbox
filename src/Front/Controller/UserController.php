<?php

namespace Martial\Warez\Front\Controller;

use Martial\Warez\Form\Login;
use Martial\Warez\Security\BadCredentialsException;
use Martial\Warez\User\UserNotFoundException;
use Martial\Warez\User\UserServiceInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserController extends AbstractController
{
    /**
     * @var UserServiceInterface
     */
    private $userService;

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
        $this->userService = $userService;
        parent::__construct($twig, $formFactory, $session, $urlGenerator);
    }

    public function login(Request $request)
    {
        $loginForm = $this->formFactory->create(new Login());
        $loginForm->handleRequest($request);

        if ($loginForm->isValid()) {
            $requestLogin = $request->request->get('login');

            try {
                $user = $this->userService->authenticateByEmail($requestLogin['email'], $requestLogin['password']);
                $this->session->set('connected', true);
                $this->session->set('username', $user->getUsername());
                $this->session->set('user_id', $user->getId());

                return new RedirectResponse($this->urlGenerator->generate('homepage'));
            } catch (BadCredentialsException $e) {
                $this->session->getFlashBag()->add('error', 'You have provided a wrong password.');
            } catch (UserNotFoundException $e) {
                $this->session->getFlashBag()->add('error', 'This email was not found.');
            }
        }

        return new Response(
            $this->twig->render('@home/index.html.twig', [
                'loginForm' => $loginForm->createView()
            ])
        );
    }

    public function logout()
    {
        $this->session->set('connected', false);
        $this->session->getFlashBag()->add('notice', 'You are logged out.');

        return new RedirectResponse($this->urlGenerator->generate('homepage'));
    }
}

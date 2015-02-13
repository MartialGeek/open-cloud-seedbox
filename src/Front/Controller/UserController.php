<?php

namespace Martial\Warez\Front\Controller;

use Martial\Warez\Form\Login;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
{
    public function login(Request $request)
    {
        $loginForm = $this->formFactory->create(new Login());
        $loginForm->handleRequest($request);

        if ($loginForm->isValid()) {
            $this->session->set('connected', true);
            $this->session->getFlashBag()->add('notice', 'You are logged in.');

            return new RedirectResponse($this->urlGenerator->generate('homepage'));
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

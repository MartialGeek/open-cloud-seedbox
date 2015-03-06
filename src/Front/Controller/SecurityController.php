<?php

namespace Martial\Warez\Front\Controller;

use Martial\Warez\Form\Login;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends AbstractController
{
    public function loginForm()
    {
        $loginForm = $this->formFactory->create(new Login());

        return new Response(
            $this->twig->render('@security/loginForm.html.twig', [
                'loginForm' => $loginForm->createView()
            ])
        );
    }
}

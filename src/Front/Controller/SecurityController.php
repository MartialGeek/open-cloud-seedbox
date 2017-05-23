<?php

namespace Martial\OpenCloudSeedbox\Front\Controller;

use Martial\OpenCloudSeedbox\Form\Login;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends AbstractController
{
    public function loginForm()
    {
        $loginForm = $this->formFactory->create(Login::class);

        return new Response(
            $this->twig->render('@security/loginForm.html.twig', [
                'loginForm' => $loginForm->createView()
            ])
        );
    }
}

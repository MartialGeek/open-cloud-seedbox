<?php

namespace Martial\OpenCloudSeedbox\Front\Controller;

use Martial\OpenCloudSeedbox\Form\Login;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractController
{
    /**
     * @return Response
     */
    public function index()
    {
        $loginForm = $this->formFactory->create(new Login());

        return new Response(
            $this->twig->render('@home/index.html.twig', [
                'loginForm' => $loginForm->createView()
            ])
        );
    }
}

<?php

namespace Martial\OpenCloudSeedbox\Front\Controller;

use Martial\OpenCloudSeedbox\Form\Login;
use Martial\OpenCloudSeedbox\Security\BadCredentialsException;
use Martial\OpenCloudSeedbox\Security\CookieTokenizerInterface;
use Martial\OpenCloudSeedbox\User\UserNotFoundException;
use Martial\OpenCloudSeedbox\User\UserServiceInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserController extends AbstractController
{
    /**
     * @var CookieTokenizerInterface
     */
    private $cookieTokenizer;

    /**
     * @param \Twig_Environment $twig
     * @param FormFactoryInterface $formFactory
     * @param Session $session
     * @param UrlGeneratorInterface $urlGenerator
     * @param UserServiceInterface $userService
     * @param CookieTokenizerInterface $cookieTokenizer
     */
    public function __construct(
        \Twig_Environment $twig,
        FormFactoryInterface $formFactory,
        Session $session,
        UrlGeneratorInterface $urlGenerator,
        UserServiceInterface $userService,
        CookieTokenizerInterface $cookieTokenizer
    ) {
        parent::__construct($twig, $formFactory, $session, $urlGenerator, $userService);
        $this->cookieTokenizer = $cookieTokenizer;
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

                $cookieToken = $this->cookieTokenizer->generateAndStoreToken($user);
                $cookie = new Cookie(
                    'remember_me',
                    json_encode([
                        'id' => $cookieToken->getTokenId(),
                        'token' => $cookieToken->getTokenHash()
                    ]),
                    new \DateTime('+1 month')
                );

                $response = new RedirectResponse($this->urlGenerator->generate('homepage'));
                $response->headers->setCookie($cookie);

                return $response;
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
        $this->session->invalidate();
        $this->session->set('connected', false);
        $this->session->getFlashBag()->add('notice', 'You are logged out.');
        $response = new RedirectResponse($this->urlGenerator->generate('homepage'));
        $response->headers->clearCookie('remember_me');

        return $response;
    }
}

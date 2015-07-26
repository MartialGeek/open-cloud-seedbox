<?php

namespace Martial\OpenCloudSeedbox\Front\Controller;

use Martial\OpenCloudSeedbox\Upload\Freebox\FreeboxAuthorizationDeniedException;
use Martial\OpenCloudSeedbox\Upload\Freebox\FreeboxAuthenticationException;
use Martial\OpenCloudSeedbox\Upload\Freebox\FreeboxAuthorizationException;
use Martial\OpenCloudSeedbox\Upload\Freebox\FreeboxAuthorizationPendingException;
use Martial\OpenCloudSeedbox\Upload\Freebox\FreeboxAuthorizationTimeoutException;
use Martial\OpenCloudSeedbox\Upload\Freebox\FreeboxManager;
use Martial\OpenCloudSeedbox\Upload\Freebox\FreeboxSessionException;
use Martial\OpenCloudSeedbox\User\UserServiceInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FreeboxController extends AbstractController
{
    /**
     * @var FreeboxManager
     */
    private $freeboxManager;

    /**
     * @param \Twig_Environment $twig
     * @param FormFactoryInterface $formFactory
     * @param Session $session
     * @param UrlGeneratorInterface $urlGenerator
     * @param UserServiceInterface $userService
     * @param FreeboxManager $freeboxManager
     */
    public function __construct(
        \Twig_Environment $twig,
        FormFactoryInterface $formFactory,
        Session $session,
        UrlGeneratorInterface $urlGenerator,
        UserServiceInterface $userService,
        FreeboxManager $freeboxManager
    ) {
        parent::__construct($twig, $formFactory, $session, $urlGenerator, $userService);
        $this->freeboxManager = $freeboxManager;
    }

    public function askUserPermission()
    {
        $response = new JsonResponse();

        try {
            $token = $this->freeboxManager->askUserPermission($this->getUser());
            $response->setData($token);
        } catch (FreeboxAuthenticationException $e) {
            $response->setData(['message' => 'An error occurred during the permission request.', 400]);
        }

        return $response;
    }

    public function getAuthorizationStatus($trackId)
    {
        $response = new JsonResponse();

        try {
            $status = $this->freeboxManager->trackAuthorizationStatus($this->getUser(), $trackId);
            $response->setData([
                'status' => 'success',
                'challenge' => $status['result']['challenge']
            ]);
        } catch (FreeboxAuthorizationDeniedException $e) {
            $response
                ->setData([
                    'status' => 'denied',
                    'message' => 'The user denied the authorization request.'
                ])
                ->setStatusCode(400);
        } catch (FreeboxAuthorizationPendingException $e) {
            $response->setData([
                'status' => 'pending',
                'message' => 'The permission is pending.'
            ]);
        } catch (FreeboxAuthorizationTimeoutException $e) {
            $response
                ->setData([
                    'status' => 'timeout',
                    'message' => 'The user did not grant the application quickly enough.'
                ])
                ->setStatusCode(400);
        } catch (FreeboxAuthorizationException $e) {
            $response
                ->setData([
                    'status' => 'unknown_error',
                    'message' => 'An unknown error occurred during the authorization demand.'
                ])
                ->setStatusCode(500);
        }

        return $response;
    }

    public function openSession(Request $request)
    {
        $appToken = $request->request->get('app_token');
        $challenge = $request->request->get('challenge');

        try {
            $this->freeboxManager->openSession($this->getUser(), $appToken, $challenge);
            return new Response('', 204);
        } catch (FreeboxSessionException $e) {
            return new JsonResponse(['message' => 'An error occurred during the session opening.', 400]);
        }
    }

    public function uploadFile($filename)
    {
        try {
            $this->freeboxManager->uploadFile($filename, $this->getUser());
            return new Response('', 204);
        } catch (FreeboxSessionException $e) {
            return new JsonResponse(['message' => 'You need to open a Freebox session before uploading files.'], 400);
        }
    }
}

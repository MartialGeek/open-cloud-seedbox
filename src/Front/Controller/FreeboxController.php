<?php

namespace Martial\Warez\Front\Controller;

use Martial\Warez\Upload\Freebox\FreeboxAuthorizationDeniedException;
use Martial\Warez\Upload\Freebox\FreeboxAuthenticationException;
use Martial\Warez\Upload\Freebox\FreeboxAuthorizationException;
use Martial\Warez\Upload\Freebox\FreeboxAuthorizationPendingException;
use Martial\Warez\Upload\Freebox\FreeboxAuthorizationTimeoutException;
use Martial\Warez\Upload\Freebox\FreeboxManager;
use Martial\Warez\Upload\Freebox\FreeboxSessionException;
use Martial\Warez\User\UserServiceInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\File;
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
     * @var string
     */
    private $downloadDir;

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

    /**
     * Sets the directory of the downloaded files.
     *
     * @param string $downloadDir
     */
    public function setDownloadDir($downloadDir)
    {
        $this->downloadDir = $downloadDir;
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
            $response->setData([
                'status' => 'denied',
                'message' => 'The user denied the authorization request.'
            ], 400);
        } catch (FreeboxAuthorizationPendingException $e) {
            $response->setData([
                'status' => 'pending',
                'message' => 'The permission is pending.'
            ]);
        } catch (FreeboxAuthorizationTimeoutException $e) {
            $response->setData([
                'status' => 'timeout',
                'message' => 'The user did not grant the application quickly enough.'
            ], 400);
        } catch (FreeboxAuthorizationException $e) {
            $response->setData([
                'status' => 'unknown_error',
                'message' => 'An unknown error occurred during the authorization demand.'
            ], 500);
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
        $file = new File($this->downloadDir . '/' . $filename);

        try {
            $this->freeboxManager->uploadFile($file, $this->getUser());
            return new Response('', 204);
        } catch (FreeboxSessionException $e) {
            return new JsonResponse(['message' => 'You need to open a Freebox session before uploading files.'], 400);
        }
    }
}

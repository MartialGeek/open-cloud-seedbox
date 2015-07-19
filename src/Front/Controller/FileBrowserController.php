<?php

namespace Martial\Warez\Front\Controller;

use Martial\Warez\Filesystem\FileBrowserInterface;
use Martial\Warez\Filesystem\PermissionDeniedException;
use Martial\Warez\User\UserServiceInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FileBrowserController extends AbstractController
{
    /**
     * @var FileBrowserInterface
     */
    private $fileBrowser;

    /**
     * @param \Twig_Environment $twig
     * @param FormFactoryInterface $formFactory
     * @param Session $session
     * @param UrlGeneratorInterface $urlGenerator
     * @param UserServiceInterface $userService
     * @param FileBrowserInterface $fileBrowser
     */
    public function __construct(
        \Twig_Environment $twig,
        FormFactoryInterface $formFactory,
        Session $session,
        UrlGeneratorInterface $urlGenerator,
        UserServiceInterface $userService,
        FileBrowserInterface $fileBrowser
    ) {
        parent::__construct($twig, $formFactory, $session, $urlGenerator, $userService);
        $this->fileBrowser = $fileBrowser;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function browse(Request $request)
    {
        $path = $request->get('path', '/');

        try {
            $items = $this->fileBrowser->browse($path);
        } catch (PermissionDeniedException $e) {
            $message = sprintf(
                'Unable to open the directory %s with this error message: "%s"',
                $e->getPath(),
                $e->getMessage()
            );
            $this->session->getFlashBag()->add('error', $message);

            return new RedirectResponse($this->urlGenerator->generate('file_browser', ['path' => '/']));
        }

        return new Response($this->twig->render('@file_browser/browse.html.twig', ['items' => $items]));
    }
}

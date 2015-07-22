<?php

namespace Martial\Warez\Front\Controller;

use JMS\Serializer\SerializerInterface;
use Martial\Warez\Filesystem\FileBrowserInterface;
use Martial\Warez\Filesystem\PathNotFoundException;
use Martial\Warez\Filesystem\PermissionDeniedException;
use Martial\Warez\User\UserServiceInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param \Twig_Environment $twig
     * @param FormFactoryInterface $formFactory
     * @param Session $session
     * @param UrlGeneratorInterface $urlGenerator
     * @param UserServiceInterface $userService
     * @param FileBrowserInterface $fileBrowser
     * @param SerializerInterface $serializer
     */
    public function __construct(
        \Twig_Environment $twig,
        FormFactoryInterface $formFactory,
        Session $session,
        UrlGeneratorInterface $urlGenerator,
        UserServiceInterface $userService,
        FileBrowserInterface $fileBrowser,
        SerializerInterface $serializer
    ) {
        parent::__construct($twig, $formFactory, $session, $urlGenerator, $userService);
        $this->fileBrowser = $fileBrowser;
        $this->serializer = $serializer;
    }

    /**
     * @return Response
     */
    public function browse()
    {
        return new Response($this->twig->render('@file_browser/browse.html.twig'));
    }

    /**
     * @param string $path
     * @return JsonResponse
     */
    public function path($path)
    {
        $response = new JsonResponse();

        if ('/' != $path) {
            $path = '/' . $path;
        }

        try {
            $items = $this->fileBrowser->browse($path);
        } catch (PermissionDeniedException $e) {
            $message = sprintf(
                'Unable to open the directory %s with this error message: %s',
                $e->getPath(),
                $e->getMessage()
            );

            $response
                ->setContent($this->serialize(['message' => $message]))
                ->setStatusCode(403);

            return $response;
        } catch (PathNotFoundException $e) {
            $message = sprintf(
                'Unable to open the directory %s with this error message: %s',
                $e->getPath(),
                $e->getMessage()
            );

            $response
                ->setContent($this->serialize(['message' => $message]))
                ->setStatusCode(404);

            return $response;
        }

        $response->setContent($this->serialize([
            'path' => $path,
            'parentPath' => dirname($path),
            'items' => $items
        ]));

        return $response;
    }

    /**
     * Returns the JSON representation of the given data.
     *
     * @param array $data
     * @return string
     */
    private function serialize(array $data)
    {
        return $this->serializer->serialize($data, 'json');
    }
}

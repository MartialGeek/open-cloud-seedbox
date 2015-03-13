<?php

namespace Martial\Warez\Front\Controller;

use Martial\Warez\Form\TrackerSearch;
use Martial\Warez\T411\Api\ClientInterface;
use Martial\Warez\User\ProfileServiceInterface;
use Martial\Warez\User\UserServiceInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TrackerController extends AbstractController
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var UserServiceInterface
     */
    private $userService;

    /**
     * @var ProfileServiceInterface
     */
    private $profileService;

    /**
     * @param \Twig_Environment $twig
     * @param FormFactoryInterface $formFactory
     * @param Session $session
     * @param UrlGeneratorInterface $urlGenerator
     * @param ClientInterface $client
     * @param UserServiceInterface $userService
     * @param ProfileServiceInterface $profileService
     */
    public function __construct(
        \Twig_Environment $twig,
        FormFactoryInterface $formFactory,
        Session $session,
        UrlGeneratorInterface $urlGenerator,
        ClientInterface $client,
        UserServiceInterface $userService,
        ProfileServiceInterface $profileService
    ) {
        $this->client = $client;
        $this->userService = $userService;
        $this->profileService = $profileService;
        parent::__construct($twig, $formFactory, $session, $urlGenerator);
    }

    public function search(Request $request)
    {
        $this->checkTrackerAuthentication();
        $token = $this->session->get('api_token');
        $categories = $this->client->getCategories($token);
        $searchForm = $this->formFactory->create(new TrackerSearch(), null, ['categories' => $categories]);
        $result = [];

        if ($request->query->has('tracker_search')) {
            $queryParameterSearch = $request->query->get('tracker_search');
            $searchForm->setData($queryParameterSearch);
            $queryParameterSearch['offset'] = $request->query->get('offset', 0);
            $queryParameterSearch['limit'] = $request->query->get('limit', 20);
            $result = $this->client->search($token, $queryParameterSearch);
        }

        return $this->twig->render('@tracker/search.html.twig', [
            'result' => $result,
            'searchForm' => $searchForm->createView()
        ]);
    }

    private function checkTrackerAuthentication()
    {
        if (!$this->session->has('api_token')) {
            $profile = $this->userService->find($this->session->get('user_id'))->getProfile();
            $this->profileService->decodeTrackerPassword($profile);

            $this->session->set('api_token', $this->client->authenticate(
                $profile->getTrackerUsername(),
                $profile->getTrackerPassword()
            ));
        }
    }
}

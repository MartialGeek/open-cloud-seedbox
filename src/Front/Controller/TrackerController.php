<?php

namespace Martial\Warez\Front\Controller;

use Martial\Warez\Download\TorrentClientException;
use Martial\Warez\Download\TorrentClientInterface;
use Martial\Warez\Download\TransmissionSessionTrait;
use Martial\Warez\Form\TrackerSearch;
use Martial\Warez\Settings\TrackerSettings;
use Martial\Warez\T411\Api\ClientInterface;
use Martial\Warez\User\UserServiceInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TrackerController extends AbstractController
{
    use TransmissionSessionTrait;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var UserServiceInterface
     */
    private $userService;

    /**
     * @var TrackerSettings
     */
    private $settingsManager;

    /**
     * @var TorrentClientInterface
     */
    private $torrentClient;

    /**
     * @param \Twig_Environment $twig
     * @param FormFactoryInterface $formFactory
     * @param Session $session
     * @param UrlGeneratorInterface $urlGenerator
     * @param ClientInterface $client
     * @param UserServiceInterface $userService
     * @param TrackerSettings $settingsManager
     * @param TorrentClientInterface $torrentClient
     */
    public function __construct(
        \Twig_Environment $twig,
        FormFactoryInterface $formFactory,
        Session $session,
        UrlGeneratorInterface $urlGenerator,
        ClientInterface $client,
        UserServiceInterface $userService,
        TrackerSettings $settingsManager,
        TorrentClientInterface $torrentClient
    ) {
        $this->client = $client;
        $this->userService = $userService;
        $this->settingsManager = $settingsManager;
        $this->torrentClient = $torrentClient;
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

    public function download($torrentId)
    {
        $this->checkTrackerAuthentication();
        $torrent = $this->client->download($this->session->get('api_token'), $torrentId);
        $sessionId = $this->getSessionId($this->session, $this->torrentClient);

        try {
            $this->torrentClient->addToQueue($sessionId, $torrent);
        } catch (TorrentClientException $e) {
            return new Response($e->getMessage(), 500);
        }

        return new Response('', 200);
    }

    private function checkTrackerAuthentication()
    {
        if (!$this->session->has('api_token')) {
            $user = $this->userService->find($this->session->get('user_id'));
            $settings = $this->settingsManager->getSettings($user);

            $this->session->set('api_token', $this->client->authenticate(
                $settings->getUsername(),
                $settings->getPassword()
            ));
        }
    }
}

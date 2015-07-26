<?php

namespace Martial\OpenCloudSeedbox\Front\Controller;

use Martial\OpenCloudSeedbox\Download\TorrentClientException;
use Martial\OpenCloudSeedbox\Download\TorrentClientInterface;
use Martial\OpenCloudSeedbox\Download\TransmissionSessionTrait;
use Martial\OpenCloudSeedbox\Form\TrackerSearch;
use Martial\OpenCloudSeedbox\Settings\IncompleteSettingsException;
use Martial\OpenCloudSeedbox\Settings\SettingsManagerInterface;
use Martial\OpenCloudSeedbox\Settings\TrackerSettings;
use Martial\T411\Api\ClientInterface;
use Martial\OpenCloudSeedbox\User\UserServiceInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
     * @param UserServiceInterface $userService
     * @param ClientInterface $client
     * @param SettingsManagerInterface $settingsManager
     * @param TorrentClientInterface $torrentClient
     */
    public function __construct(
        \Twig_Environment $twig,
        FormFactoryInterface $formFactory,
        Session $session,
        UrlGeneratorInterface $urlGenerator,
        UserServiceInterface $userService,
        ClientInterface $client,
        SettingsManagerInterface $settingsManager,
        TorrentClientInterface $torrentClient
    ) {
        $this->client = $client;
        $this->settingsManager = $settingsManager;
        $this->torrentClient = $torrentClient;
        parent::__construct($twig, $formFactory, $session, $urlGenerator, $userService);
    }

    public function search(Request $request)
    {
        try {
            $this->checkTrackerAuthentication();
        } catch (IncompleteSettingsException $e) {
            $this->session->getFlashBag()->add('error', $e->getMessage());

            return new RedirectResponse($this->urlGenerator->generate('homepage'));
        }

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
        try {
            $this->checkTrackerAuthentication();
        } catch (IncompleteSettingsException $e) {
            $this->session->getFlashBag()->add('error', $e->getMessage());

            return new RedirectResponse($this->urlGenerator->generate('homepage'));
        }

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
            $settings = $this->settingsManager->getSettings($this->getUser());

            if (!$this->settingsManager->isComplete($settings)) {
                throw new IncompleteSettingsException(
                    'Your must complete your settings before using the torrent search.'
                );
            }

            $this->session->set('api_token', $this->client->authenticate(
                $settings->getUsername(),
                $settings->getPassword()
            ));
        }
    }
}

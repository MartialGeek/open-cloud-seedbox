<?php

namespace Martial\Warez\Front\Controller;

use GuzzleHttp\Exception\RequestException;
use Martial\Warez\Download\TorrentClientInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TransmissionController extends AbstractController
{
    /**
     * @var TorrentClientInterface
     */
    private $torrentClient;

    /**
     * @param \Twig_Environment $twig
     * @param FormFactoryInterface $formFactory
     * @param Session $session
     * @param UrlGeneratorInterface $urlGenerator
     * @param TorrentClientInterface $torrentClient
     */
    public function __construct(
        \Twig_Environment $twig,
        FormFactoryInterface $formFactory,
        Session $session,
        UrlGeneratorInterface $urlGenerator,
        TorrentClientInterface $torrentClient
    ) {
        $this->torrentClient = $torrentClient;
        parent::__construct($twig, $formFactory, $session, $urlGenerator);
    }

    public function torrentList()
    {
        $sessionId = $this->session->has('transmission_session_id') ?
            $this->session->get('transmission_session_id') :
            $this->torrentClient->getSessionId();

        return $this->twig->render('@transmission/torrent-list.html.twig', [
            'torrents' => $this->torrentClient->getTorrentList($sessionId)
        ]);
    }
}

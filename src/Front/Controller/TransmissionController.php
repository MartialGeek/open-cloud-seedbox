<?php

namespace Martial\Warez\Front\Controller;

use Martial\Warez\Download\TorrentClientInterface;
use Martial\Warez\Download\TransmissionSessionTrait;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TransmissionController extends AbstractController
{
    use TransmissionSessionTrait;

    /**
     * @var TorrentClientInterface
     */
    private $torrentClient;

    /**
     * @var string
     */
    private $transmissionSessionId;

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
        parent::__construct($twig, $formFactory, $session, $urlGenerator);
        $this->torrentClient = $torrentClient;
        $this->transmissionSessionId = $this->getSessionId($this->session, $this->torrentClient);
    }

    public function torrentList()
    {
        return $this->twig->render('@transmission/torrent-list.html.twig', [
            'torrents' => $this->torrentClient->getTorrentList($this->transmissionSessionId)
        ]);
    }

    public function torrentData($torrentId)
    {
        $data = $this->torrentClient->getTorrentData($this->transmissionSessionId, $torrentId);

        return new JsonResponse($data);
    }
}

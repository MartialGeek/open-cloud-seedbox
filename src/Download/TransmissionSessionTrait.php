<?php

namespace Martial\Warez\Download;

use Symfony\Component\HttpFoundation\Session\Session;

trait TransmissionSessionTrait
{
    /**
     * Fetches the transmission session ID from the session if it has been already stored, or calls
     * @param Session $session
     * @param TorrentClientInterface $torrentClient
     * @return string
     */
    public function getSessionId(Session $session, TorrentClientInterface $torrentClient)
    {
        return $session->has('transmission_session_id') ?
            $session->get('transmission_session_id') :
            $torrentClient->getSessionId();
    }
}

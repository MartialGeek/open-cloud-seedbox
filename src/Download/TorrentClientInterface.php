<?php

namespace Martial\Warez\Download;

use Symfony\Component\HttpFoundation\File\File;

interface TorrentClientInterface
{
    /**
     * Adds a torrent in the download queue.
     *
     * @param string $sessionId
     * @param File $torrent
     * @throws TorrentClientException
     */
    public function addToQueue($sessionId, File $torrent);

    public function removeFromQueue();

    /**
     * @param string $sessionId
     * @return array
     */
    public function getTorrentList($sessionId);

    public function getTorrentData();

    /**
     * Returns the session ID.
     *
     * @return string
     */
    public function getSessionId();
}

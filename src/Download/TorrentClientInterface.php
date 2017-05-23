<?php

namespace Martial\OpenCloudSeedbox\Download;

interface TorrentClientInterface
{
    /**
     * Adds a torrent in the download queue.
     *
     * @param string $sessionId
     * @param \SplFileInfo $torrent
     * @throws TorrentClientException
     */
    public function addToQueue($sessionId, \SplFileInfo $torrent);

    /**
     * Removes the given torrent ID from the queue.
     *
     * @param string $sessionId
     * @param int $torrentId
     * @throws TorrentClientException
     */
    public function removeFromQueue($sessionId, $torrentId);

    /**
     * Returns an array of the torrents in the queue.
     *
     * @param string $sessionId
     * @return array
     * @throws TorrentClientException
     */
    public function getTorrentList($sessionId);

    /**
     * Returns an array of data related to the given torrent ID.
     *
     * @param string $sessionId
     * @param int $torrentId
     * @return array
     * @throws TorrentClientException
     */
    public function getTorrentData($sessionId, $torrentId);

    /**
     * Returns the session ID.
     *
     * @return string
     * @throws TorrentClientException
     */
    public function getSessionId();
}

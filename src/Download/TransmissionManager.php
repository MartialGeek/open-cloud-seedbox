<?php

namespace Martial\Warez\Download;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\File\File;

class TransmissionManager implements TorrentClientInterface
{
    const TORRENT_STATUS_STOPPED = 0;
    const TORRENT_STATUS_CHECK_WAITING = 1;
    const TORRENT_STATUS_CHECKING = 2;
    const TORRENT_STATUS_DOWNLOAD_WAITING = 3;
    const TORRENT_STATUS_DOWNLOADING = 4;
    const TORRENT_STATUS_SEED_WAITING = 5;
    const TORRENT_STATUS_SEEDING = 6;

    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var array
     */
    private $config;

    /**
     * @param ClientInterface $httpClient
     * @param array $config
     */
    public function __construct(ClientInterface $httpClient, array $config)
    {
        $this->httpClient = $httpClient;
        $this->config = $config;
    }

    /**
     * Adds a torrent in the download queue.
     *
     * @param string $sessionId
     * @param File $torrent
     * @throws TorrentClientException
     */
    public function addToQueue($sessionId, File $torrent)
    {
        $body = '{"method": "torrent-add", "arguments": {"filename": "' . $torrent->getPathname() . '"}}';
        $response = $this->sendRequest($sessionId, $body)->json();

        if ($response['result'] != 'success') {
            throw new TorrentClientException(
                sprintf(
                    'The torrent could not be added to the queue: %s',
                    $response['result']
                )
            );
        }
    }

    /**
     * Removes the given torrent ID from the queue.
     *
     * @param string $sessionId
     * @param int $torrentId
     */
    public function removeFromQueue($sessionId, $torrentId)
    {
        // TODO: Implement removeFromQueue() method.
    }

    /**
     * Returns an array of the torrents in the queue.
     *
     * @param string $sessionId
     * @return array
     */
    public function getTorrentList($sessionId)
    {
        $body = '{"method": "torrent-get", "arguments": {"fields": ' . $this->getTorrentFields() . '}}';

        $response = $this->sendRequest($sessionId, $body)->json();

        return $response['arguments']['torrents'];
    }

    /**
     * Returns an array of data related to the given torrent ID.
     *
     * @param string $sessionId
     * @param int $torrentId
     * @return array
     */
    public function getTorrentData($sessionId, $torrentId)
    {
        $body = '{
            "method": "torrent-get",
            "arguments": {
                "ids": [' . $torrentId . '],
                "fields": ' . $this->getTorrentFields() .
            '}
        }';

        $response = $this->sendRequest($sessionId, $body)->json();

        return $response['arguments']['torrents'][0];
    }

    /**
     * Returns the session ID.
     *
     * @return string
     */
    public function getSessionId()
    {
        $sessionId = '';

        try {
            $this->getTorrentList('');
        } catch (RequestException $e) {
            $sessionId = $e->getResponse()->getHeader('X-Transmission-Session-Id');
        }

        return $sessionId;
    }

    /**
     * Sends the request to the Transmission API.
     *
     * @param string $sessionId
     * @param string $body
     * @return \GuzzleHttp\Message\ResponseInterface
     */
    protected function sendRequest($sessionId, $body)
    {
        return $this
            ->httpClient
            ->post($this->config['rpc_uri'], [
                'body' => $body,
                'auth' => [$this->config['login'], $this->config['password']],
                'headers' => [
                    'X-Transmission-Session-Id' => $sessionId
                ]
            ]);
    }

    /**
     * Returns a JSON array of the torrent's fields.
     *
     * @return string
     */
    protected function getTorrentFields()
    {
        return '[
            "id",
            "name",
            "addedDate",
            "downloadedEver",
            "isFinished",
            "isStalled",
            "leftUntilDone",
            "peers",
            "percentDone",
            "queuePosition",
            "rateDownload",
            "rateUpload",
            "secondsDownloading",
            "secondsSeeding",
            "startDate",
            "status",
            "trackers",
            "totalSize",
            "torrentFile",
            "uploadedEver",
            "wanted"
        ]';
    }
}

<?php

namespace Martial\Warez\Download;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\File\File;

class TransmissionManager implements TorrentClientInterface
{
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

    public function removeFromQueue()
    {
        // TODO: Implement removeFromQueue() method.
    }

    /**
     * @param string $sessionId
     * @return array
     */
    public function getTorrentList($sessionId)
    {
        $body = '{"method": "torrent-get", "arguments": {"fields": ["id", "name"]}}';
        $response = $this->sendRequest($sessionId, $body)->json();

        return $response['arguments']['torrents'];
    }

    public function getTorrentData()
    {
        // TODO: Implement getTorrentData() method.
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
     * @param $sessionId
     * @param $body
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
}

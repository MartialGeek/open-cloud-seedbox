<?php

namespace Martial\Warez\Tests\Download;

use GuzzleHttp\Exception\RequestException;
use Martial\Warez\Download\TransmissionManager;

class TransmissionManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $httpClient;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $response;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $requestException;

    /**
     * @var array
     */
    public $config;

    /**
     * @var TransmissionManager
     */
    public $transmissionManager;

    /**
     * @var string
     */
    public $sessionId;

    public function testGetTorrentList()
    {
        $this->getTorrentList();
    }

    public function testAddToQueue()
    {
        $this->addToQueue();
    }

    /**
     * @expectedException \Martial\Warez\Download\TorrentClientException
     */
    public function testAddToQueueWithFailure()
    {
        $this->addToQueue(false);
    }

    public function testGetTorrentData()
    {
        $torrentId = 42;
        $body = '{
            "method": "torrent-get",
            "arguments": {
                "ids": [' . $torrentId . '],
                "fields": ' . $this->getTorrentFields() .
            '}
        }';

        $responseData = [
            'arguments' => [
                'torrents' => [
                    [
                        'name' => 'A torrent name'
                    ]
                ]
            ]
        ];

        $this->sendRequest($body);
        $this->toJson($responseData);
        $result = $this->transmissionManager->getTorrentData($this->sessionId, $torrentId);
        $this->assertSame($responseData['arguments']['torrents'][0], $result);
    }

    public function testGetSessionId()
    {
        try {
            $this->getTorrentList(false);
        } catch (RequestException $e) {

        }

        $this
            ->requestException
            ->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->response);

        $this
            ->response
            ->expects($this->once())
            ->method('getHeader')
            ->with($this->equalTo('X-Transmission-Session-Id'))
            ->willReturn($this->sessionId);

        $sessionId = $this->transmissionManager->getSessionId();
        $this->assertSame($this->sessionId, $sessionId);
    }

    protected function setUp()
    {
        $this->httpClient = $this->getMock('\GuzzleHttp\ClientInterface');
        $this->config = [
            'login' => 'Toto',
            'password' => 'p@sSw0rd',
            'host' => 'localhost',
            'port' => 9091,
            'rpc_uri' => '/transmission/rpc'
        ];
        $this->transmissionManager = new TransmissionManager($this->httpClient, $this->config);
        $this->sessionId = uniqid();
        $this->response = $this->getMock('\GuzzleHttp\Message\ResponseInterface');
        $this->requestException = $this
            ->getMockBuilder('GuzzleHttp\Exception\RequestException')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getTorrentFields()
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

    /**
     * @param bool $success
     */
    private function addToQueue($success = true)
    {
        $torrent = new \SplFileInfo(__FILE__ . '/../Resources/T411/ubuntu-14.04-desktop-amd64.iso.torrent');
        $body = '{"method": "torrent-add", "arguments": {"filename": "' . $torrent->getPathname() . '"}}';

        $responseToArray = [
            'result' => $success ? 'success' : 'fail'
        ];

        $this->sendRequest($body);
        $this->toJson($responseToArray);

        $this->transmissionManager->addToQueue($this->sessionId, $torrent);
    }

    /**
     * @param string $body
     * @param bool $success
     */
    private function sendRequest($body, $success = true)
    {
        $invocation = $this
            ->httpClient
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->equalTo($this->config['rpc_uri']),
                $this->equalTo([
                    'body' => $body,
                    'auth' => [$this->config['login'], $this->config['password']],
                    'headers' => [
                        'X-Transmission-Session-Id' => $success ? $this->sessionId : ''
                    ]
                ])
            );

        if ($success) {
            $invocation->willReturn($this->response);
        } else {
            $invocation->willThrowException($this->requestException);
        }
    }

    /**
     * @param array $responseData
     */
    private function toJson(array $responseData)
    {
        $this
            ->response
            ->expects($this->once())
            ->method('json')
            ->willReturn($responseData);
    }

    /**
     * @param bool $success
     */
    private function getTorrentList($success = true)
    {
        $body = '{"method": "torrent-get", "arguments": {"fields": '. $this->getTorrentFields() .'}}';
        $responseJSON = file_get_contents(__DIR__ . '/../Resources/Transmission/getTorrents.json');
        $responseArray = json_decode($responseJSON, true);

        $this->sendRequest($body, $success);

        if ($success) {
            $this->toJson([
                'arguments' => [
                    'torrents' => $responseArray
                ]
            ]);

            $list = $this->transmissionManager->getTorrentList($this->sessionId);
            $this->assertSame($responseArray, $list);
        }
    }
}

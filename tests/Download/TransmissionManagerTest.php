<?php

namespace Martial\Warez\Tests\Download;

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
        $body = '{"method": "torrent-get", "arguments": {"fields": '. $this->getTorrentFields() .'}}';
        $responseJSON = file_get_contents(__DIR__ . '/../Resources/Transmission/getTorrents.json');
        $responseArray = json_decode($responseJSON, true);

        $this
            ->httpClient
            ->expects($this->once())
            ->method('post')
            ->with($this->equalTo($this->config['rpc_uri']), $this->equalTo([
                'body' => $body,
                'auth' => [$this->config['login'], $this->config['password']],
                'headers' => ['X-Transmission-Session-Id' => $this->sessionId]
            ]))
            ->willReturn($this->response);

        $this
            ->response
            ->expects($this->once())
            ->method('json')
            ->willReturn([
                'arguments' => [
                    'torrents' => $responseArray
                ]
            ]);

        $list = $this->transmissionManager->getTorrentList($this->sessionId);
        $this->assertSame($responseArray, $list);
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
    }

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

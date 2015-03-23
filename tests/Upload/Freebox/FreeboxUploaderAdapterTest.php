<?php

namespace Martial\Warez\Tests\Upload\Freebox;

use Martial\Warez\Upload\Freebox\FreeboxUploaderAdapter;
use Symfony\Component\HttpFoundation\File\File;

class FreeboxUploaderAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $httpClient;

    /**
     * @var array
     */
    public $config;

    /**
     * @var FreeboxUploaderAdapter
     */
    public $freeboxAdapter;

    public function testUploadFile()
    {
        $authorizeResponse = $this->createResponse();
        $authorizeTrackIdResponse = $this->createResponse();
        $challengeResponse = $this->createResponse();
        $sessionResponse = $this->createResponse();

        $authorizeResponseData = [
            'success' => true,
            'result' => [
                'app_token' => uniqid(),
                'track_id' => 42
            ]
        ];

        $authorizeTrackIdResponseData = [
            'success' => true,
            'result' => [
                'status' => 'granted',
                'challenge' => uniqid()
            ]
        ];

        $challengeResponseData = [
            'success' => true,
            'result' => [
                'logged_in' => false,
                'challenge' => uniqid()
            ]
        ];

        $sessionResponseData = [
            'success' => true,
            'result' => [
                'session_token' => uniqid(),
                'challenge' => uniqid(),
                'permissions' => [
                    'downloaded' => true
                ]
            ]

        ];

        $this
            ->httpClient
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                [$this->equalTo('/api/v3/login/authorize/' . $authorizeResponseData['result']['track_id'])],
                [$this->equalTo('/api/v3/login')]
            )
            ->willReturnOnConsecutiveCalls(
                $authorizeTrackIdResponse,
                $challengeResponse
            );

        $authorizeTrackIdResponse
            ->expects($this->once())
            ->method('json')
            ->willReturn($authorizeTrackIdResponseData);

        $challengeResponse
            ->expects($this->once())
            ->method('json')
            ->willReturn($challengeResponseData);

        $this
            ->httpClient
            ->expects($this->exactly(2))
            ->method('post')
            ->withConsecutive(
                [
                    $this->equalTo('/api/v3/login/authorize'),
                    $this->equalTo([
                        'body' => [
                            'app_id' => $this->config['app_id'],
                            'app_name' => $this->config['app_name'],
                            'app_version' => $this->config['app_version'],
                            'device_name' => $this->config['device_name'],
                        ]
                    ])
                ],
                [
                    $this->equalTo('/api/v3/login/session'),
                    $this->equalTo([
                        'body' => [
                            'app_id' => $this->config['app_id'],
                            'password' => hash_hmac(
                                'sha1',
                                $authorizeResponseData['result']['app_token'],
                                $challengeResponseData['result']['challenge']
                            )
                        ]
                    ])
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $authorizeResponse,
                $sessionResponse
            );

        $authorizeResponse
            ->expects($this->once())
            ->method('json')
            ->willReturn($authorizeResponseData);

        $sessionResponse
            ->expects($this->once())
            ->method('json')
            ->willReturn($sessionResponseData);

        $this->freeboxAdapter->upload(new File('/tmp/file', false));
    }

    protected function setUp()
    {
        $this->httpClient = $this->getMock('\GuzzleHttp\ClientInterface');
        $this->config = [
            'host' => '45.56.56.87',
            'port' => 8888,
            'app_id' => 'net.warez-manager',
            'app_name' => 'Warez Manager',
            'app_version' => '1.0',
            'device_name' => 'seedbox'
        ];

        $this->freeboxAdapter = new FreeboxUploaderAdapter($this->httpClient, $this->config);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createResponse()
    {
        return $this->getMock('\GuzzleHttp\Message\ResponseInterface');
    }
}

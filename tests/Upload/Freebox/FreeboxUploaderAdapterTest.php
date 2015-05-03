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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $urlResolver;

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
        $uploadedFile = new File('/tmp/file', false);
        $uploadUrl = 'http://www.warez.io/files/download/your-file.avi';
        $sessionToken = uniqid();

        $addDownloadResponse = $this->createResponse();

        $addDownloadResponseData = [
            'success' => true,
            'result' => [
                'id' => 42
            ]
        ];

        $this
            ->urlResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($this->equalTo($uploadedFile))
            ->willReturn($uploadUrl);

        $this
            ->httpClient
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->equalTo('/api/v3/downloads/add'),
                $this->equalTo([
                    'body' => [
                        'download_url' => $uploadUrl
                    ],
                    'headers' => [
                        'X-Fbx-App-Auth' => $sessionToken
                    ]
                ])
            )
            ->willReturn($addDownloadResponse);

        $addDownloadResponse
            ->expects($this->once())
            ->method('json')
            ->willReturn($addDownloadResponseData);

        $this->freeboxAdapter->upload($uploadedFile, [$sessionToken]);
    }

    protected function setUp()
    {
        $this->httpClient = $this->getMock('\GuzzleHttp\ClientInterface');
        $this->urlResolver = $this->getMock('\Martial\Warez\Upload\UploadUrlResolverInterface');
        $this->config = [
            'app_id' => 'net.warez-manager',
            'app_name' => 'Warez Manager',
            'app_version' => '1.0',
            'device_name' => 'seedbox'
        ];

        $this->freeboxAdapter = new FreeboxUploaderAdapter($this->httpClient, $this->urlResolver);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createResponse()
    {
        return $this->getMock('\GuzzleHttp\Message\ResponseInterface');
    }
}

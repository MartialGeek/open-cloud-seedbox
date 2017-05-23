<?php

namespace Martial\OpenCloudSeedbox\Tests\Upload\Freebox;

use GuzzleHttp\ClientInterface;
use Martial\OpenCloudSeedbox\Upload\Freebox\FreeboxUploaderAdapter;
use Martial\OpenCloudSeedbox\Upload\UploadException;
use Martial\OpenCloudSeedbox\Upload\UploadUrlResolverInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
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

    public function testUploadRegularFile()
    {
        $this->upload();
    }

    public function testUploadArchive()
    {
        $this->upload('archive');
    }

    public function testUploadReturnsAnError()
    {
        $exceptionThrown = false;

        try {
            $this->upload('regular', false);
        } catch (UploadException $e) {
            $exceptionThrown = true;
            $this->assertSame('Oops!', $e->getMessage());
        }

        if (!$exceptionThrown) {
            $this->fail('The expected exception \Martial\OpenCloudSeedbox\Upload\UploadException was not thrown.');
        }
    }

    protected function setUp()
    {
        $this->httpClient = $this->getMock(ClientInterface::class);
        $this->urlResolver = $this->getMock(UploadUrlResolverInterface::class);
        $this->config = [
            'app_id' => 'net.open-cloud-seedbox',
            'app_name' => 'Open Cloud Seedbox',
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
        return $this->getMock(ResponseInterface::class);
    }

    private function upload($uploadType = 'regular', $success = true)
    {
        $freeboxUrl = 'http://66.66.66.66:8888';
        $uploadedFile = new File('/tmp/file', false);
        $filename = urlencode('/path/to/file.avi');
        $uploadUrl = 'http://www.seedbox.io/files/download/?filename=' . $filename . '&upload-type=' . $uploadType;
        $sessionToken = uniqid();

        $addDownloadResponse = $this->createResponse();

        $addDownloadResponseData = [
            'success' => $success,
            'result' => [
                'id' => 42
            ]
        ];

        $addDownloadResponseData['msg'] = 'Oops!';

        $this
            ->urlResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($this->equalTo($uploadedFile), $this->equalTo(['upload_type' => $uploadType]))
            ->willReturn($uploadUrl);

        $this
            ->httpClient
            ->expects($this->once())
            ->method('request')
            ->with('POST', $freeboxUrl . '/api/v3/downloads/add', [
                'form_params' => [
                    'download_url' => $uploadUrl
                ],
                'headers' => [
                    'X-Fbx-App-Auth' => $sessionToken
                ]
            ])
            ->willReturn($addDownloadResponse);

        $stream = $this
            ->getMockBuilder(StreamInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $addDownloadResponse
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($stream);

        $stream
            ->expects($this->once())
            ->method('getContents')
            ->willReturn(json_encode($addDownloadResponseData, JSON_FORCE_OBJECT));

        $this->freeboxAdapter->upload($uploadedFile, $freeboxUrl, [
            'session_token' => $sessionToken,
            'upload_type' => $uploadType
        ]);
    }
}

<?php

namespace Martial\Warez\Upload\Freebox;

use GuzzleHttp\ClientInterface;
use Martial\Warez\Upload\UploadException;
use Martial\Warez\Upload\UploadInterface;
use Martial\Warez\Upload\UploadUrlResolverInterface;
use Symfony\Component\HttpFoundation\File\File;

class FreeboxUploaderAdapter implements UploadInterface
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var UploadUrlResolverInterface
     */
    private $urlResolver;

    /**
     * @param ClientInterface $client
     * @param UploadUrlResolverInterface $resolver
     */
    public function __construct(ClientInterface $client, UploadUrlResolverInterface $resolver)
    {
        $this->httpClient = $client;
        $this->urlResolver = $resolver;
    }

    /**
     * Uploads the given file on the target.
     *
     * @param File $file
     * @param string $targetUrl
     * @param array $config
     * @throws UploadException
     */
    public function upload(File $file, $targetUrl, array $config = array())
    {
        $downloadUrl = $this->urlResolver->resolve($file, [
            'upload_type' => isset($config['type']) ? $config['type'] : FreeboxManager::DOWNLOAD_TYPE_REGULAR
        ]);

        $response = $this
            ->httpClient
            ->post($targetUrl . '/api/v3/downloads/add', [
                'body' => [
                    'download_url' => $downloadUrl
                ],
                'headers' => [
                    'X-Fbx-App-Auth' => $config['session_token']
                ]
            ])
            ->json();

        if (!$response['success']) {
            throw new UploadException($response['msg']);
        }
    }
}

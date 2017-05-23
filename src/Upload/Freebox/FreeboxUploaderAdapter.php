<?php

namespace Martial\OpenCloudSeedbox\Upload\Freebox;

use GuzzleHttp\ClientInterface;
use Martial\OpenCloudSeedbox\Upload\UploadException;
use Martial\OpenCloudSeedbox\Upload\UploadInterface;
use Martial\OpenCloudSeedbox\Upload\UploadUrlResolverInterface;
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
        $uploadType = isset($config['upload_type']) ? $config['upload_type'] : FreeboxManager::UPLOAD_TYPE_REGULAR;

        $rawrResponse = $this
            ->httpClient
            ->request('POST', $targetUrl . '/api/v3/downloads/add', [
                'form_params' => [
                    'download_url' => $this->urlResolver->resolve($file, ['upload_type' => $uploadType])
                ],
                'headers' => [
                    'X-Fbx-App-Auth' => $config['session_token']
                ]
            ])
            ->getBody()
            ->getContents();

        $response = \GuzzleHttp\json_decode($rawrResponse, true);

        if (!$response['success']) {
            throw new UploadException($response['msg']);
        }
    }
}

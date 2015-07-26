<?php

namespace Martial\OpenCloudSeedbox\Upload;

use GuzzleHttp\ClientInterface;
use Martial\OpenCloudSeedbox\Upload\Freebox\FreeboxUploaderAdapter;

class UploadAdapterFactory implements UploadAdapterFactoryInterface
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
     * Returns an instance of the given adapter or throws an exception if the adapter is not supported.
     *
     * @param string $adapter
     * @return UploadInterface
     */
    public function get($adapter)
    {
        if ('freebox' != strtolower($adapter)) {
            throw new \InvalidArgumentException(
                sprintf('Unsupported %s upload adapter.', $adapter)
            );
        }

        return new FreeboxUploaderAdapter($this->httpClient, $this->urlResolver);
    }
}

<?php

namespace Martial\Warez\Upload;

use GuzzleHttp\ClientInterface;
use Martial\Warez\Upload\Freebox\FreeboxUploaderAdapter;

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
     * @param array $config
     * @return UploadInterface
     */
    public function get($adapter, array $config)
    {
        if ('freebox' != strtolower($adapter)) {
            throw new \InvalidArgumentException(
                sprintf('Unsupported %s upload adapter.', $adapter)
            );
        }

        return new FreeboxUploaderAdapter($this->httpClient, $this->urlResolver, $config);
    }
}

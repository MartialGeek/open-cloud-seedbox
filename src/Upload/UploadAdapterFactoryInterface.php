<?php

namespace Martial\Warez\Upload;

interface UploadAdapterFactoryInterface
{
    /**
     * Returns an instance of the given adapter or throws an exception if the adapter is not supported.
     *
     * @param string $adapter
     * @param array $config
     * @return UploadInterface
     */
    public function get($adapter, array $config);
}

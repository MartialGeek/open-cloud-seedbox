<?php

namespace Martial\OpenCloudSeedbox\Upload;

interface UploadAdapterFactoryInterface
{
    /**
     * Returns an instance of the given adapter or throws an exception if the adapter is not supported.
     *
     * @param string $adapter
     * @return UploadInterface
     */
    public function get($adapter);
}

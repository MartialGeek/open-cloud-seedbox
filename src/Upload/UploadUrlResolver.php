<?php

namespace Martial\OpenCloudSeedbox\Upload;

use Martial\OpenCloudSeedbox\Upload\Freebox\FreeboxManager;
use Symfony\Component\HttpFoundation\File\File;

class UploadUrlResolver implements UploadUrlResolverInterface
{
    const UPLOAD_URI = '/upload/';

    /**
     * @var string
     */
    private $host;

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * Returns the public URL which exposes the given file.
     *
     * @param File $file
     * @param array $options
     * @return string
     */
    public function resolve(File $file, array $options = [])
    {
        $uploadType = isset($options['upload_type']) ? $options['upload_type'] : FreeboxManager::UPLOAD_TYPE_REGULAR;

        $url = sprintf(
            '%s%s?filename=%s&upload-type=%s',
            $this->host,
            self::UPLOAD_URI,
            urlencode($file->getPathname()),
            $uploadType
        );

        return $url;
    }
}

<?php

namespace Martial\Warez\Upload;

use Martial\Warez\Upload\Freebox\FreeboxManager;
use Symfony\Component\HttpFoundation\File\File;

class UploadUrlResolver implements UploadUrlResolverInterface
{
    const UPLOAD_URI = '/upload';

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
        $uri  = self::UPLOAD_URI . '?filename=' . urlencode($file->getPathname());
        $uri .= '&type=' . isset($options['type']) ? $options['type'] : FreeboxManager::DOWNLOAD_TYPE_REGULAR;

        return $this->host . $uri;
    }
}

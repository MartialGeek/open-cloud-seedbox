<?php

namespace Martial\Warez\Upload;

use Martial\Warez\Upload\Freebox\FreeboxManager;
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
     * @return string
     */
    public function resolve(File $file)
    {
        return $this->host .  self::UPLOAD_URI . '?filename=' . urlencode($file->getPathname());
    }
}

<?php

namespace Martial\Warez\Upload;

use Symfony\Component\HttpFoundation\File\File;

class UploadUrlResolver implements UploadUrlResolverInterface
{
    const UPLOAD_URI = '/upload/{filename}';

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
        $uri = str_replace('{filename}', $file->getFilename(), self::UPLOAD_URI);

        return $this->host . $uri;
    }
}

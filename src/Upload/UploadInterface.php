<?php

namespace Martial\Warez\Upload;

use Symfony\Component\HttpFoundation\File\File;

interface UploadInterface
{
    /**
     * Uploads the given file on the target.
     *
     * @param File $file
     * @param string $targetUrl
     * @param array $config
     * @throws UploadException
     */
    public function upload(File $file, $targetUrl, array $config = array());
}

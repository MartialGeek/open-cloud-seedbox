<?php

namespace Martial\Warez\Upload;

use Symfony\Component\HttpFoundation\File\File;

interface UploadUrlResolverInterface
{
    /**
     * Returns the public URL which exposes the given file.
     *
     * @param File $file
     * @return string
     */
    public function resolve(File $file);
}

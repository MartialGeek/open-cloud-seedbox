<?php

namespace Martial\Warez\Upload;

use Symfony\Component\HttpFoundation\File\File;

class UploadUrlResolver implements UploadUrlResolverInterface
{
    /**
     * Returns the public URL which exposes the given file.
     *
     * @param File $file
     * @return string
     */
    public function resolve(File $file)
    {
        // TODO: Implement resolve() method.
    }
}

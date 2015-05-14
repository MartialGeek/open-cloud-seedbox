<?php

namespace Martial\Warez\Upload;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RequestStack;

class UploadUrlResolver implements UploadUrlResolverInterface
{
    const UPLOAD_URI = '/upload/{filename}';

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
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

        return $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost() . $uri;
    }
}

<?php

namespace Martial\Warez\Upload;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UploadUrlResolver implements UploadUrlResolverInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Returns the public URL which exposes the given file.
     *
     * @param File $file
     * @return string
     */
    public function resolve(File $file)
    {
        return $this->urlGenerator->generate(
            'upload_file', ['filename' => $file->getFilename()], UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}

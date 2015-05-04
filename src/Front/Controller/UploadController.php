<?php

namespace Martial\Warez\Front\Controller;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class UploadController
{
    /**
     * @var string
     */
    private $downloadDir;

    /**
     * @param string $downloadDir
     */
    public function setDownloadDir($downloadDir)
    {
        $this->downloadDir = $downloadDir;
    }

    public function upload($filename)
    {
        $file = new File($this->downloadDir . '/' . $filename);
        $response = new BinaryFileResponse($file);
        $response->headers->set('Content-Type', $file->getMimeType());

        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $file->getFilename()
        );

        return $response;
    }
}

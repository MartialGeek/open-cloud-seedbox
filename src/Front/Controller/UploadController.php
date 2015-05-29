<?php

namespace Martial\Warez\Front\Controller;

use Martial\Warez\Upload\Freebox\FreeboxManager;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class UploadController
{
    /**
     * @var string
     */
    private $downloadDir;

    /**
     * @var string
     */
    private $archiveDir;

    /**
     * @param string $downloadDir
     */
    public function setDownloadDir($downloadDir)
    {
        $this->downloadDir = $downloadDir;
    }

    /**
     * @param string $archiveDir
     */
    public function setArchiveDir($archiveDir)
    {
        $this->archiveDir = $archiveDir;
    }

    public function upload(Request $request)
    {
        $filename = $request->query->get('filename');
        $file = new File($filename);
        $response = new BinaryFileResponse($file);
        $response->headers->set('Content-Type', $file->getMimeType() . '; charset=UTF-8');

        $response
            ->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $file->getFilename()
            )
            ->deleteFileAfterSend(true);

        return $response;
    }
}

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
        $type = $request->query->get('type', FreeboxManager::DOWNLOAD_TYPE_REGULAR);

        if ($type == FreeboxManager::DOWNLOAD_TYPE_REGULAR) {
            $filePath = $this->downloadDir;
        } elseif ($type == FreeboxManager::DOWNLOAD_TYPE_ARCHIVE) {
            $filePath = $this->archiveDir;
        } else {
            return new Response('Unknown value for the parameter "type"');
        }

        $filePath .= '/' . $filename;
        $file = new File($filePath);
        $response = new BinaryFileResponse($file);
        $response->headers->set('Content-Type', $file->getMimeType() . '; charset=UTF-8');

        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $file->getFilename()
        );

        return $response;
    }
}

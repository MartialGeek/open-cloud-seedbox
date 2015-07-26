<?php

namespace Martial\OpenCloudSeedbox\Front\Controller;

use Martial\OpenCloudSeedbox\Upload\Freebox\FreeboxManager;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

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
        $uploadType = $request->query->get('upload-type', FreeboxManager::UPLOAD_TYPE_REGULAR);
        $file = new File($filename);
        $response = new BinaryFileResponse($file);
        $response->headers->set('Content-Type', $file->getMimeType() . '; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $file->getFilename() . '"');

        if ($uploadType == FreeboxManager::UPLOAD_TYPE_ARCHIVE) {
            $response->deleteFileAfterSend(true);
        }

        return $response;
    }
}

<?php

namespace Martial\Warez\Filesystem;

class ZipArchiver
{
    /**
     * @param \SplFileInfo $file
     * @param $archivePath
     * @throws \RuntimeException
     */
    public function createArchive(\SplFileInfo $file, $archivePath)
    {
        $archive = new \ZipArchive();

        if ($archive->open($archivePath, \ZipArchive::CREATE) !== true) {
            throw new \RuntimeException('Unable to create the archive ' . $archivePath);
        }

        $archive->addFile($file->getPathname());
        $archive->close();
    }
}

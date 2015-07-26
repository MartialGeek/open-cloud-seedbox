<?php

namespace Martial\OpenCloudSeedbox\Filesystem;

use Alchemy\Zippy\Zippy;

class ZipArchiver
{
    /**
     * @var Zippy
     */
    private $zippy;

    /**
     * @param Zippy $zippy
     */
    public function __construct(Zippy $zippy)
    {
        $this->zippy = $zippy;
    }

    /**
     * @param \SplFileInfo $file
     * @param $archivePath
     * @throws \RuntimeException
     */
    public function createArchive(\SplFileInfo $file, $archivePath)
    {
        ini_set('max_execution_time', 0);
        $structure = [];

        if ($file->isDir()) {
            $structure[$file->getFilename()] = $file->getRealPath();
        } else {
            $structure[] = $file->getRealPath();
        }

        $this->zippy->create($archivePath, $structure);
    }
}

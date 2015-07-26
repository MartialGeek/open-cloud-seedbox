<?php

namespace Martial\OpenCloudSeedbox\Filesystem;

use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\ExclusionPolicy("all")
 * @JMS\AccessorOrder("custom", custom={"filename", "isDir", "relativePath"})
 */
class File
{
    /**
     * @var \SplFileInfo
     */
    private $file;

    /**
     * @var string
     */
    private $rootPath;

    /**
     * @param \SplFileInfo $file
     * @param string $rootPath
     */
    public function __construct(\SplFileInfo $file, $rootPath)
    {
        $this->file = $file;
        $this->rootPath = $rootPath;
    }

    /**
     * Returns the relative path regarding the file browser root path.
     *
     * @return string
     * @JMS\VirtualProperty()
     */
    public function getRelativePath()
    {
        return str_replace($this->rootPath, '', $this->file->getPathname());
    }

    /**
     * Returns true is the file is a directory.
     *
     * @return bool
     * @JMS\VirtualProperty()
     */
    public function isDir()
    {
        return $this->file->isDir();
    }

    /**
     * Returns the file name of the file.
     *
     * @return string
     * @JMS\VirtualProperty()
     */
    public function getFilename()
    {
        return $this->file->getFilename();
    }

    /**
     * Returns the full path of the file.
     *
     * @return string
     * @JMS\VirtualProperty()
     */
    public function getFullPath()
    {
        return $this->file->getPathname();
    }
}

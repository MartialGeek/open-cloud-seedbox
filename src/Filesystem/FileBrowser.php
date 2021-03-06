<?php

namespace Martial\OpenCloudSeedbox\Filesystem;

class FileBrowser implements FileBrowserInterface
{
    /**
     * @var string
     */
    private $rootPath;

    /**
     * @param string $rootPath
     */
    public function __construct($rootPath)
    {
        if (!$rootPath) {
            throw new \InvalidArgumentException(sprintf('The path %s does not exist.', $rootPath));
        }

        if (!is_dir($rootPath)) {
            throw new \InvalidArgumentException(sprintf('The path %s is not a directory.', $rootPath));
        }

        if (!is_readable($rootPath)) {
            throw new \InvalidArgumentException(sprintf('The path %s is not readable.', $rootPath));
        }

        $this->rootPath = $rootPath;
    }

    /**
     * Returns an array file \Martial\OpenCloudSeedbox\Filesystem\File or throws a
     * \Martial\OpenCloudSeedbox\Filesystem\PermissionDeniedException.
     *
     * @param string $path
     * @return File[]
     * @throws PermissionDeniedException
     * @throws PathNotFoundException
     */
    public function browse($path)
    {
        $fullPath = $this->rootPath . $path;

        if (!file_exists($fullPath)) {
            $e = new PathNotFoundException('Path not found.');
            $e->setPath($path);
            throw $e;
        }

        if (strpos($path, '../') !== false) {
            $e = new PermissionDeniedException('Path with ../ characters are not allowed.');
            $e->setPath($path);
            throw $e;
        }

        $items = [];

        if (!is_readable($fullPath)) {
            $e = new PermissionDeniedException('Path not readable.');
            $e->setPath($path);
            throw $e;
        }

        $root = new \FilesystemIterator($fullPath);

        foreach ($root as $item) {
            $items[] = new File($item, $this->rootPath);
        }

        return $items;
    }
}

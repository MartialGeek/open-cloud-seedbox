<?php

namespace Martial\OpenCloudSeedbox\Filesystem;

interface FileBrowserInterface
{
    /**
     * Returns an array file \Martial\OpenCloudSeedbox\Filesystem\File or throws a
     * \Martial\OpenCloudSeedbox\Filesystem\PermissionDeniedException.
     *
     * @param string $path
     * @return File[]
     * @throws PermissionDeniedException
     * @throws PathNotFoundException
     */
    public function browse($path);
}

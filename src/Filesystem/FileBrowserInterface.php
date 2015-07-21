<?php

namespace Martial\Warez\Filesystem;

interface FileBrowserInterface
{
    /**
     * Returns an array file \Martial\Warez\Filesystem\File or throws a
     * \Martial\Warez\Filesystem\PermissionDeniedException.
     *
     * @param string $path
     * @return File[]
     * @throws PermissionDeniedException
     * @throws PathNotFoundException
     */
    public function browse($path);
}

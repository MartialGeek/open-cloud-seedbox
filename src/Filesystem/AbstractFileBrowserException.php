<?php

namespace Martial\Warez\Filesystem;

class AbstractFileBrowserException extends \Exception
{
    /**
     * @var string
     */
    private $path;

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}

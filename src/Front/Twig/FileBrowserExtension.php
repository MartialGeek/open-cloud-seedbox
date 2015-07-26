<?php

namespace Martial\OpenCloudSeedbox\Front\Twig;

class FileBrowserExtension extends \Twig_Extension
{
    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'file_browser';
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('parentPath', [$this, 'parentPath']),
        ];
    }

    /**
     * Returns the parent path of the given path.
     *
     * @param string $path
     * @return string
     */
    public function parentPath($path)
    {
        return dirname(dirname($path));
    }
}

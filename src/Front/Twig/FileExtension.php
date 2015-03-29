<?php

namespace Martial\Warez\Front\Twig;


class FileExtension extends \Twig_Extension
{
    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'file';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('convertToHumanReadable', [$this, 'convertToHumanReadable'])
        ];
    }

    /**
     * Returns a human readable value of a file size in octets.
     *
     * @param int $size
     * @return string
     */
    public function convertToHumanReadable($size)
    {
        return $size > 999999999 ?
            round($size / 1000000000, 2) . ' GB' :
            round($size / 1000000, 2) . ' MB';
    }
}

<?php

namespace Martial\OpenCloudSeedbox\Front\Twig;

use Martial\OpenCloudSeedbox\Download\TransmissionManager;

class TransmissionExtension extends \Twig_Extension
{
    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'transmission';
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('torrentStatusLabel', [$this, 'getTorrentStatusLabel']),
            new \Twig_SimpleFunction('computeDownloadedSize', [$this, 'computeDownloadedSize']),
        ];
    }

    /**
     * Returns the downloaded size of a file.
     *
     * @param float $totalSize
     * @param float $percentDone    From the RPC API, range from 0 to 1
     * @return float
     */
    public function computeDownloadedSize($totalSize, $percentDone)
    {
        return (float) $totalSize * (float) $percentDone;
    }

    /**
     * Returns the torrent status label corresponding to the given status code.
     *
     * @param int $statusCode
     * @return string
     */
    public function getTorrentStatusLabel($statusCode)
    {
        switch ($statusCode) {
            case $statusCode === TransmissionManager::TORRENT_STATUS_STOPPED:
                $label = 'Stopped';
                break;
            case $statusCode === TransmissionManager::TORRENT_STATUS_CHECK_WAITING:
                $label = 'Wait for checking';
                break;
            case $statusCode === TransmissionManager::TORRENT_STATUS_CHECKING:
                $label = 'Checking';
                break;
            case $statusCode === TransmissionManager::TORRENT_STATUS_DOWNLOAD_WAITING:
                $label = 'Wait for downloading';
                break;
            case $statusCode === TransmissionManager::TORRENT_STATUS_DOWNLOADING:
                $label = 'Downloading';
                break;
            case $statusCode === TransmissionManager::TORRENT_STATUS_SEED_WAITING:
                $label = 'Wait for seeding';
                break;
            case $statusCode === TransmissionManager::TORRENT_STATUS_SEEDING:
                $label = 'Seeding';
                break;
            default:
                $label = 'Unknown status';
        }

        return $label;
    }
}

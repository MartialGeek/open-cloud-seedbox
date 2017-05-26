<?php

namespace Martial\OpenCloudSeedbox\Download;

use Martial\Transmission\API\Argument\Torrent\Add;
use Martial\Transmission\API\Argument\Torrent\Get;
use Martial\Transmission\API\CSRFException;
use Martial\Transmission\API\TorrentIdList;
use Martial\Transmission\API\TransmissionAPI;

class TransmissionManager implements TorrentClientInterface
{
    const TORRENT_STATUS_STOPPED = 0;
    const TORRENT_STATUS_CHECK_WAITING = 1;
    const TORRENT_STATUS_CHECKING = 2;
    const TORRENT_STATUS_DOWNLOAD_WAITING = 3;
    const TORRENT_STATUS_DOWNLOADING = 4;
    const TORRENT_STATUS_SEED_WAITING = 5;
    const TORRENT_STATUS_SEEDING = 6;

    /**
     * @var TransmissionAPI
     */
    private $rpcClient;

    /**
     * @param TransmissionAPI $rpcClient
     */
    public function __construct(TransmissionAPI $rpcClient)
    {
        $this->rpcClient = $rpcClient;
    }

    /**
     * Adds a torrent in the download queue.
     *
     * @param string $sessionId
     * @param \SplFileInfo $torrent
     * @throws TorrentClientException
     */
    public function addToQueue($sessionId, \SplFileInfo $torrent)
    {
        try {
            $this->rpcClient->torrentAdd($sessionId, [
                Add::FILENAME => $torrent->getPathname()
            ]);
        } catch (\Exception $e) {
            throw new TorrentClientException(
                sprintf('Unable to add the torrent %s in the queue', $torrent->getFilename()),
                0,
                $e
            );
        }
    }

    /**
     * Removes the given torrent ID from the queue.
     *
     * @param string $sessionId
     * @param int $torrentId
     * @throws TorrentClientException
     */
    public function removeFromQueue($sessionId, $torrentId)
    {
        try {
            $this->rpcClient->torrentRemove($sessionId, new TorrentIdList([$torrentId]));
        } catch (\Exception $e) {
            throw new TorrentClientException(
                sprintf('Unable to remove the torrent ID %d from the queue', $torrentId),
                0,
                $e
            );
        }
    }

    /**
     * Returns an array of the torrents in the queue.
     *
     * @param string $sessionId
     * @return array
     * @throws TorrentClientException
     */
    public function getTorrentList($sessionId)
    {
        try {
            $torrents = $this->rpcClient->torrentGet($sessionId, new TorrentIdList([]), $this->getTorrentFields());
        } catch (\Exception $e) {
            throw new TorrentClientException(
                'Unable to retrieve the torrents list',
                0,
                $e
            );
        }

        return $torrents;
    }

    /**
     * Returns an array of data related to the given torrent ID.
     *
     * @param string $sessionId
     * @param int $torrentId
     * @return array
     * @throws TorrentClientException
     */
    public function getTorrentData($sessionId, $torrentId)
    {
        try {
            $torrentData = $this
                ->rpcClient
                ->torrentGet($sessionId, new TorrentIdList([$torrentId]), $this->getTorrentFields());
        } catch (\Exception $e) {
            throw new TorrentClientException(
                sprintf('Unable to retrieve the data of the torrent ID %d', $torrentId),
                0,
                $e
            );
        }

        return $torrentData[0];
    }

    /**
     * Returns the session ID.
     *
     * @return string
     */
    public function getSessionId()
    {
        $sessionId = '';

        try {
            $this->rpcClient->sessionGet($sessionId);
        } catch (CSRFException $e) {
            $sessionId = $e->getSessionId();
        }

        return $sessionId;
    }

    /**
     * Returns a JSON array of the torrent's fields.
     *
     * @return array
     */
    protected function getTorrentFields()
    {
        return [
            Get::ID,
            Get::NAME,
            Get::ADDED_DATE,
            Get::IS_FINISHED,
            Get::IS_STALLED,
            Get::LEFT_UNTIL_DONE,
            Get::PEERS,
            Get::PERCENT_DONE,
            Get::QUEUE_POSITION,
            Get::RATE_DOWNLOAD,
            Get::RATE_UPLOAD,
            Get::SECONDS_DOWNLOADING,
            Get::SECONDS_SEEDING,
            Get::START_DATE,
            Get::STATUS,
            Get::TRACKERS,
            Get::TOTAL_SIZE,
            Get::TORRENT_FILE,
            Get::UPLOAD_EVER,
            Get::WANTED,
        ];
    }
}

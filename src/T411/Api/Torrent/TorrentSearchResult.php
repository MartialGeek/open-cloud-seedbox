<?php

namespace Martial\Warez\T411\Api\Torrent;


class TorrentSearchResult implements TorrentSearchResultInterface
{
    /**
     * @var string
     */
    private $query;

    /**
     * @var int
     */
    private $total;

    /**
     * @var int
     */
    private $offset;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var TorrentInterface[]
     */
    private $torrents;

    public function __construct()
    {
        $this->torrents = array();
    }

    /**
     * Sets the query used for the search.
     *
     * @param string $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * Retrieves the query used for the search.
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Sets the total of results.
     *
     * @param int $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * Retrieves the total of results.
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Sets the current offset.
     *
     * @param int $offset
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    /**
     * Retrieves the current offset.
     *
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Sets the limit of results in the collection.
     *
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * Retrieves the limit of results in the collection.
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Sets the collection of torrents.
     *
     * @param TorrentInterface[] $torrents
     */
    public function setTorrents(array $torrents)
    {
        $this->torrents = $torrents;
    }

    /**
     * Retrieves the collection of torrents.
     *
     * @return TorrentInterface[]
     */
    public function getTorrents()
    {
        return $this->torrents;
    }
}

<?php

namespace Martial\Warez\T411\Api\Torrent;


interface TorrentSearchResultInterface
{
    /**
     * Sets the query used for the search.
     *
     * @param string $query
     */
    public function setQuery($query);

    /**
     * Retrieves the query used for the search.
     *
     * @return string
     */
    public function getQuery();

    /**
     * Sets the total of results.
     *
     * @param int $total
     */
    public function setTotal($total);

    /**
     * Retrieves the total of results.
     *
     * @return int
     */
    public function getTotal();

    /**
     * Sets the current offset.
     *
     * @param int $offset
     */
    public function setOffset($offset);

    /**
     * Retrieves the current offset.
     *
     * @return int
     */
    public function getOffset();

    /**
     * Sets the limit of results in the collection.
     *
     * @param int $limit
     */
    public function setLimit($limit);

    /**
     * Retrieves the limit of results in the collection.
     *
     * @return int
     */
    public function getLimit();

    /**
     * Sets the collection of torrents.
     *
     * @param TorrentInterface[] $torrents
     */
    public function setTorrents(array $torrents);

    /**
     * Retrieves the collection of torrents.
     *
     * @return TorrentInterface[]
     */
    public function getTorrents();
}

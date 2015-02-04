<?php

namespace Martial\Warez\Tests\T411\Api\Torrent;


use Martial\Warez\T411\Api\Torrent\Torrent;
use Martial\Warez\T411\Api\Torrent\TorrentSearchResult;

class TorrentSearchResultTest extends \PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $torrentSearchResult = new TorrentSearchResult();
        $query = 'avatar';
        $offset = 10;
        $limit = 100;
        $total = 25;
        $torrents = [];

        for ($i = 0; $i < 5; $i++) {
            $torrents[] = new Torrent();
        }

        $torrentSearchResult->setQuery($query);
        $torrentSearchResult->setOffset($offset);
        $torrentSearchResult->setLimit($limit);
        $torrentSearchResult->setTotal($total);
        $torrentSearchResult->setTorrents($torrents);

        $this->assertSame($query, $torrentSearchResult->getQuery());
        $this->assertSame($offset, $torrentSearchResult->getOffset());
        $this->assertSame($limit, $torrentSearchResult->getLimit());
        $this->assertSame($total, $torrentSearchResult->getTotal());
        $this->assertSame($torrents, $torrentSearchResult->getTorrents());
    }
}

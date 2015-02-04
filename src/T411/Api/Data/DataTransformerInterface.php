<?php

namespace Martial\Warez\T411\Api\Data;

use Martial\Warez\T411\Api\Category\CategoryInterface;
use Martial\Warez\T411\Api\Torrent\TorrentSearchResultInterface;

interface DataTransformerInterface
{
    /**
     * Builds the list of the categories from the API response.
     *
     * @param array $response
     * @return CategoryInterface[]
     */
    public function extractCategoriesFromApiResponse(array $response);

    /**
     * Builds the list of the torrents from the API response.
     *
     * @param array $response
     * @return TorrentSearchResultInterface
     */
    public function extractTorrentsFromApiResponse(array $response);
}

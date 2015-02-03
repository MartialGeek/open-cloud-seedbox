<?php

namespace Martial\Warez\T411\Api\Data;

use Martial\Warez\T411\Api\Category\Category;
use Martial\Warez\T411\Api\Category\CategoryInterface;
use Martial\Warez\T411\Api\Torrent\Torrent;
use Martial\Warez\T411\Api\Torrent\TorrentInterface;
use Martial\Warez\T411\Api\User\User;

class DataTransformer implements DataTransformerInterface
{
    /**
     * Builds the list of the categories from the API response.
     *
     * @param array $response
     * @return CategoryInterface[]
     */
    public function extractCategoriesFromApiResponse(array $response)
    {
        $categories = [];

        foreach ($response as $category) {
            if (!isset($category['id'])) {
                continue;
            }

            $cat = new Category();
            $cat->setId($category['id']);
            $cat->setName($category['name']);

            if (isset($category['cats'])) {
                $subCategories = [];

                foreach ($category['cats'] as $subCategory) {
                    $subCat = new Category();
                    $subCat->setId($subCategory['id']);
                    $subCat->setName($subCategory['name']);
                    $subCat->setParentCategory($cat);
                    $subCategories[] = $subCat;
                }

                $cat->setSubCategories($subCategories);
            }

            $categories[] = $cat;
        }

        return $categories;
    }

    /**
     * Builds the list of the torrents from the API response.
     *
     * @param array $response
     * @return TorrentInterface[]
     */
    public function extractTorrentsFromApiResponse(array $response)
    {
        $torrents = [];

        if (!isset($response['torrents']) || empty($response['torrents'])) {
            return $torrents;
        }

        foreach ($response['torrents'] as $rawTorrentData) {
            $torrent = new Torrent();
            $category = new Category();
            $owner = new User();

            $category->setId($rawTorrentData['category']);
            $category->setName($rawTorrentData['categoryname']);

            $owner->setId($rawTorrentData['owner']);
            $owner->setUploadedData($rawTorrentData['username']);

            $torrent->setId($rawTorrentData['id']);
            $torrent->setName($rawTorrentData['name']);
            $torrent->setCategory($category);
            $torrent->setNumberOfLeechers($rawTorrentData['leechers']);
            $torrent->setNumberOfSeeders($rawTorrentData['seeders']);
            $torrent->setNumberOfComments($rawTorrentData['comments']);
            $torrent->setIsVerified($rawTorrentData['isVerified']);
            $torrent->setAdditionDate(new \DateTime($rawTorrentData['added']));
            $torrent->setSize($rawTorrentData['size']);
            $torrent->setTimesCompleted($rawTorrentData['times_completed']);
        }

        return $torrents;
    }
}

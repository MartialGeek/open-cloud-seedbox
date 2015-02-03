<?php

namespace Martial\Warez\Tests\T411\Api\Data;

use Martial\Warez\T411\Api\Data\DataTransformer;

class DataTransformerTest extends \PHPUnit_Framework_TestCase
{
    const NO_TORRENTS_FOUND = 'no_torrents_found';

    /**
     * @var DataTransformer
     */
    public $transformer;

    public function testExtractCategoriesFromApiResponse()
    {
        $apiResponse = include __DIR__ . '/../mockCategoriesResponse.php';
        $categories = $this->transformer->extractCategoriesFromApiResponse($apiResponse);
        $categoryInterface = '\Martial\Warez\T411\Api\Category\CategoryInterface';
        $this->assertContainsOnly($categoryInterface, $categories);

        foreach ($categories as $category) {
            $this->assertContainsOnly($categoryInterface, $category->getSubCategories());
        }
    }

    public function testExtractTorrentsFromApiResponse()
    {
        $this->extractTorrents();
    }

    public function testExtractTorrentsShouldReturnAnEmptyArrayIfNoTorrentsAreFound()
    {
        $this->extractTorrents(self::NO_TORRENTS_FOUND);
    }

    protected function setUp()
    {
        $this->transformer = new DataTransformer();
    }

    protected function extractTorrents($context = '')
    {
        $apiResponse = include __DIR__ . '/../mockTorrentsResponse.php';

        if ($context === self::NO_TORRENTS_FOUND) {
            unset($apiResponse['torrents']);
            $torrents = $this->transformer->extractTorrentsFromApiResponse($apiResponse);
            $this->assertEmpty($torrents);
        } else {
            $torrents = $this->transformer->extractTorrentsFromApiResponse($apiResponse);
            $this->assertContainsOnly('\Martial\Warez\T411\Api\Torrent\TorrentInterface', $torrents);
        }
    }
}

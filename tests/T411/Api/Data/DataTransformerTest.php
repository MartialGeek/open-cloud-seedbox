<?php

namespace Martial\Warez\Tests\T411\Api\Data;

use Martial\Warez\T411\Api\Data\DataTransformer;

class DataTransformerTest extends \PHPUnit_Framework_TestCase
{
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
        $apiResponse = include __DIR__ . '/../mockTorrentsResponse.php';
        $torrents = $this->transformer->extractTorrentsFromApiResponse($apiResponse);
        $this->assertContainsOnly('\Martial\Warez\T411\Api\Torrent\TorrentInterface', $torrents);
    }

    protected function setUp()
    {
        $this->transformer = new DataTransformer();
    }
}

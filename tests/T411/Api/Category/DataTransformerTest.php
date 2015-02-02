<?php

namespace Martial\Warez\Tests\T411\Api\Category;

use Martial\Warez\T411\Api\Category\DataTransformer;

class DataTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testExtractCategoriesFromApiResponse()
    {
        $apiResponse = include __DIR__ . '/../mockCategoriesResponse.php';
        $transformer = new DataTransformer();
        $categories = $transformer->extractCategoriesFromApiResponse($apiResponse);
        $categoryInterface = '\Martial\Warez\T411\Api\Category\CategoryInterface';
        $this->assertContainsOnly($categoryInterface, $categories);

        foreach ($categories as $category) {
            $this->assertContainsOnly($categoryInterface, $category->getSubCategories());
        }
    }
}

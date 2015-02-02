<?php

namespace Martial\Warez\Tests\T411\Api\Category;

use Martial\Warez\T411\Api\Category\Category;

class CategoryTest extends \PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $root = new Category();
        $root->setName('Video');
        $root->setId(102);

        $subCategoryMovies = new Category();
        $subCategoryMovies->setId(202);
        $subCategoryMovies->setName('Movies');
        $subCategoryMovies->setParentCategory($root);

        $subCategoryCartoons = new Category();
        $subCategoryCartoons->setId(203);
        $subCategoryCartoons->setName('Cartoons');
        $subCategoryCartoons->setParentCategory($root);

        $root->setSubCategories([
            $subCategoryMovies,
            $subCategoryCartoons
        ]);

        $categoryInterface = '\Martial\Warez\T411\Api\Category\CategoryInterface';
        $this->assertInstanceOf($categoryInterface, $root);
        $this->assertContainsOnly($categoryInterface, $root->getSubCategories());
    }
}

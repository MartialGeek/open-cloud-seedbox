<?php

namespace Martial\Warez\T411\Api\Category;

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
}

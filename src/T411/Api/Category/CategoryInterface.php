<?php

namespace Martial\Warez\T411\Api\Category;


interface CategoryInterface
{
    /**
     * Sets the ID of the category.
     *
     * @param int $id
     */
    public function setId($id);

    /**
     * Retrieves the ID of the category.
     * @return int
     */
    public function getId();

    /**
     * Sets the name of the category.
     *
     * @param string $name
     */
    public function setName($name);

    /**
     * Retrieves the name of the category.
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the sub categories.
     *
     * @param CategoryInterface[] $subCategories
     */
    public function setSubCategories(array $subCategories);

    /**
     * Retrieve the sub categories.
     *
     * @return CategoryInterface[]
     */
    public function getSubCategories();

    /**
     * Sets the parent category.
     *
     * @param CategoryInterface $parentCategory
     */
    public function setParentCategory(CategoryInterface $parentCategory);

    /**
     * Get the parent category.
     *
     * @return CategoryInterface
     */
    public function getParentCategory();
}

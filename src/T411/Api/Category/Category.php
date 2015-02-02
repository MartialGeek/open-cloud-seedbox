<?php

namespace Martial\Warez\T411\Api\Category;


class Category implements CategoryInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var CategoryInterface[]
     */
    private $subCategories;

    /**
     * @var CategoryInterface
     */
    private $parentCategory;

    public function __construct()
    {
        $this->subCategories = array();
    }

    /**
     * Sets the ID of the category.
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Retrieves the ID of the category.
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the name of the category.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Retrieves the name of the category.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the sub categories.
     *
     * @param CategoryInterface[] $subCategories
     */
    public function setSubCategories(array $subCategories)
    {
        $this->subCategories = $subCategories;
    }

    /**
     * Retrieve the sub categories.
     *
     * @return CategoryInterface[]
     */
    public function getSubCategories()
    {
        return $this->subCategories;
    }

    /**
     * Sets the parent category.
     *
     * @param CategoryInterface $parentCategory
     */
    public function setParentCategory(CategoryInterface $parentCategory)
    {
        $this->parentCategory = $parentCategory;
    }

    /**
     * Get the parent category.
     *
     * @return CategoryInterface
     */
    public function getParentCategory()
    {
        return $this->parentCategory;
    }
}

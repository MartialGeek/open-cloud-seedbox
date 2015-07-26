<?php

namespace Martial\OpenCloudSeedbox\Tests\Form;

use Martial\OpenCloudSeedbox\Form\TrackerSearch;
use Symfony\Component\Form\FormTypeInterface;

class TrackerSearchTest extends FormTestCase
{
    /**
     * @var TrackerSearch
     */
    public $form;

    public function testFormBuilder()
    {
        $firstCategory = $this->getCategory();
        $secondCategory = $this->getCategory();
        $subCategory = $this->getCategory();

        $firstCategoryId = 12;
        $firstCategoryName = 'Film';

        $secondCategoryId = 13;
        $secondCategoryName = 'Music';

        $subCategoryId = 15;
        $subCategoryName = 'Animation';

        $categories = [
            $firstCategoryId => $firstCategoryName,
            $subCategoryId => $subCategoryName,
            $secondCategoryId => $secondCategoryName
        ];

        $this->setCategoryBehavior($firstCategory, 'getId', $firstCategoryId);
        $this->setCategoryBehavior($firstCategory, 'getName', $firstCategoryName);
        $this->setCategoryBehavior($firstCategory, 'getSubCategories', [$subCategory]);
        $this->setCategoryBehavior($subCategory, 'getId', $subCategoryId);
        $this->setCategoryBehavior($subCategory, 'getName', $subCategoryName);
        $this->setCategoryBehavior($secondCategory, 'getId', $secondCategoryId);
        $this->setCategoryBehavior($secondCategory, 'getName', $secondCategoryName);
        $this->setCategoryBehavior($secondCategory, 'getSubCategories', []);

        $this
            ->formBuilder
            ->expects($this->exactly(2))
            ->method('add')
            ->withConsecutive(
                [$this->equalTo('terms'), $this->equalTo('text')],
                [$this->equalTo('category_id'), $this->equalTo('choice'), $this->equalTo([
                    'choices' => $categories
                ])]
            )
            ->will($this->returnValue($this->formBuilder));

        $this->getForm()->buildForm($this->formBuilder, [
            'categories' => [
                $firstCategory,
                $secondCategory
            ]
        ]);
    }

    public function testDefaultOptions()
    {
        $this
            ->resolver
            ->expects($this->once())
            ->method('setRequired')
            ->with($this->equalTo(['categories']))
            ->will($this->returnValue($this->resolver));

        $this
            ->resolver
            ->expects($this->once())
            ->method('setAllowedTypes')
            ->with($this->equalTo(['categories' => 'array']))
            ->will($this->returnValue($this->resolver));

        $this
            ->resolver
            ->expects($this->once())
            ->method('setDefaults')
            ->with($this->equalTo(['csrf_protection' => false]))
            ->will($this->returnValue($this->resolver));

        $this->getForm()->setDefaultOptions($this->resolver);
    }

    /**
     * Returns the name of the form.
     *
     * @return string
     */
    protected function getFormName()
    {
        return 'tracker_search';
    }

    /**
     * Returns an instance of your form.
     *
     * @return FormTypeInterface
     */
    protected function getForm()
    {
        if (is_null($this->form)) {
            $this->form = new TrackerSearch();
        }

        return $this->form;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getCategory()
    {
        return $this->getMock('\Martial\T411\Api\Category\CategoryInterface');
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $category
     * @param string $method
     * @param string $returnedValue
     */
    private function setCategoryBehavior(\PHPUnit_Framework_MockObject_MockObject $category, $method, $returnedValue)
    {
        $category
            ->expects($this->once())
            ->method($method)
            ->will($this->returnValue($returnedValue));
    }
}

<?php

namespace Martial\OpenCloudSeedbox\Tests\Form;

use Martial\OpenCloudSeedbox\Form\TrackerSearch;
use Martial\T411\Api\Category\Category;
use Martial\T411\Api\Category\CategoryInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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

        $firstCategoryName = 'Film';
        $secondCategoryName = 'Music';
        $subCategoryId = 15;
        $subCategoryName = 'Animation';

        $categories = [
            $firstCategoryName => [
                $subCategoryName => $subCategoryId
            ],
            $secondCategoryName => []
        ];

        $this->setCategoryBehavior($firstCategory, 'getName', $firstCategoryName);
        $this->setCategoryBehavior($firstCategory, 'getSubCategories', [$subCategory]);
        $this->setCategoryBehavior($subCategory, 'getId', $subCategoryId);
        $this->setCategoryBehavior($subCategory, 'getName', $subCategoryName);
        $this->setCategoryBehavior($secondCategory, 'getName', $secondCategoryName);
        $this->setCategoryBehavior($secondCategory, 'getSubCategories', []);

        $this
            ->formBuilder
            ->expects($this->exactly(2))
            ->method('add')
            ->withConsecutive(
                [$this->equalTo('terms'), $this->equalTo(TextType::class)],
                [$this->equalTo('category_id'), $this->equalTo(ChoiceType::class), $this->equalTo([
                    'choices' => $categories,
                    'preferred_choices' => [
                        (string) Category::ID_FILM_VIDEO_MOVIE,
                        (string) Category::ID_FILM_VIDEO_TV_SERIES,
                        (string) Category::ID_AUDIO_MUSIC
                    ]
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

    public function testConfigureOptions()
    {
        $this
            ->resolver
            ->expects($this->once())
            ->method('setRequired')
            ->with(['categories'])
            ->will($this->returnValue($this->resolver));

        $this
            ->resolver
            ->expects($this->once())
            ->method('setAllowedTypes')
            ->with('categories', ['array'])
            ->will($this->returnValue($this->resolver));

        $this
            ->resolver
            ->expects($this->once())
            ->method('setDefaults')
            ->with(['csrf_protection' => false])
            ->will($this->returnValue($this->resolver));

        $this->getForm()->configureOptions($this->resolver);
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
        return $this->getMock(CategoryInterface::class);
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $category
     * @param string $method
     * @param mixed $returnedValue
     */
    private function setCategoryBehavior(\PHPUnit_Framework_MockObject_MockObject $category, $method, $returnedValue)
    {
        $category
            ->expects($this->once())
            ->method($method)
            ->will($this->returnValue($returnedValue));
    }
}

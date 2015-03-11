<?php

namespace Martial\Warez\Form;

use Martial\Warez\T411\Api\Category\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TrackerSearch extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $categories = [];

        /**
         * @var Category $category
         */
        foreach ($options['categories'] as $category) {
            $categories[$category->getId()] = $category->getName();

            foreach ($category->getSubCategories() as $subCategory) {
                $categories[$subCategory->getId()] = $subCategory->getName();
            }
        }

        $builder
            ->add('query', 'text')
            ->add('category', 'choice', [
                'choices' => $categories
            ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(['categories'])
            ->setAllowedTypes(['categories' => 'array'])
            ->setDefaults([
                'csrf_protection' => false,
            ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'tracker_search';
    }
}

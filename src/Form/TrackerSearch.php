<?php

namespace Martial\OpenCloudSeedbox\Form;

use Martial\T411\Api\Category\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrackerSearch extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $categories = $this->buildCategoriesTree($options['categories']);

        $builder
            ->add('terms', TextType::class)
            ->add('category_id', ChoiceType::class, [
                'choices' => $categories,
                'preferred_choices' => [
                    (string) Category::ID_FILM_VIDEO_MOVIE,
                    (string) Category::ID_FILM_VIDEO_TV_SERIES,
                    (string) Category::ID_AUDIO_MUSIC
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['categories'])
            ->setAllowedTypes('categories', ['array'])
            ->setDefaults([
                'csrf_protection' => false,
            ]);
    }

    /**
     * @param Category[] $categories
     * @return array
     */
    private function buildCategoriesTree(array $categories)
    {
        $tree = [];

        foreach ($categories as $category) {
            $subCategories = [];

            foreach ($category->getSubCategories() as $subCategory) {
                $subCategories[$subCategory->getName()] = (string) $subCategory->getId();
            }

            $tree[$category->getName()] = $subCategories;
        }

        return $tree;
    }
}

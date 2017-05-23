<?php

namespace Martial\OpenCloudSeedbox\Form;

use Martial\OpenCloudSeedbox\Settings\Entity\FreeboxSettingsEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FreeboxSettings extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', HiddenType::class)
            ->add('transportHost')
            ->add('transportPort')
            ->add('appId')
            ->add('appName')
            ->add('appVersion')
            ->add('deviceName');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FreeboxSettingsEntity::class
        ]);
    }
}

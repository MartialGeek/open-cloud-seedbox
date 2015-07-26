<?php

namespace Martial\OpenCloudSeedbox\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FreeboxSettings extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add('transportHost')
            ->add('transportPort')
            ->add('appId')
            ->add('appName')
            ->add('appVersion')
            ->add('deviceName');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => '\Martial\OpenCloudSeedbox\Settings\Entity\FreeboxSettingsEntity'
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'freebox_settings';
    }
}

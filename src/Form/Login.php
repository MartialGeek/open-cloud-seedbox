<?php

namespace Martial\Warez\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class Login extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'email', [
                'constraints' => [
                    new NotBlank(),
                    new Email()
                ]
            ])
            ->add('password', 'password', [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 8])
                ]
            ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'login';
    }
}

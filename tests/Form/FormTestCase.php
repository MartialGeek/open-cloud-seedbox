<?php

namespace Martial\OpenCloudSeedbox\Tests\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class FormTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FormBuilderInterface
     */
    protected $formBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|OptionsResolver
     */
    protected $resolver;

    /**
     * Returns an instance of your form.
     *
     * @return FormTypeInterface
     */
    abstract protected function getForm();

    protected function setUp()
    {
        $this->formBuilder = $this->getMock(FormBuilderInterface::class);
        $this->resolver = $this->getMock(OptionsResolver::class);
    }
}

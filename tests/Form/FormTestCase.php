<?php

namespace Martial\Warez\Tests\Form;

use Symfony\Component\Form\FormTypeInterface;

abstract class FormTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $formBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resolver;

    public function testFormName()
    {
        $this->assertSame($this->getFormName(), $this->getForm()->getName());
    }

    /**
     * Returns the name of the form.
     *
     * @return string
     */
    abstract protected function getFormName();

    /**
     * Returns an instance of your form.
     *
     * @return FormTypeInterface
     */
    abstract protected function getForm();

    protected function setUp()
    {
        $this->formBuilder = $this->getMock('\Symfony\Component\Form\FormBuilderInterface');
        $this->resolver = $this->getMock('\Symfony\Component\OptionsResolver\OptionsResolverInterface');
    }
}

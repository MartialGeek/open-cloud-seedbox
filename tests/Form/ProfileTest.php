<?php

namespace Martial\Warez\Tests\Form;

use Martial\Warez\Form\Profile;

class ProfileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Profile
     */
    public $form;

    public function testBuildForm()
    {
        $formBuilder = $this->getMock('\Symfony\Component\Form\FormBuilderInterface');

        $formBuilder
            ->expects($this->exactly(2))
            ->method('add')
            ->withConsecutive(
                [$this->equalTo('trackerUsername')],
                [
                    $this->equalTo('trackerPassword'), $this->equalTo('password'), $this->equalTo([
                        'required' => false
                    ])
                ]
            )
            ->will($this->returnValue($formBuilder));

        $this->form->buildForm($formBuilder, []);
    }

    public function testDefaultOptions()
    {
        $resolver = $this->getMock('\Symfony\Component\OptionsResolver\OptionsResolverInterface');

        $resolver
            ->expects($this->once())
            ->method('setDefaults')
            ->with($this->equalTo(['data_class' => '\Martial\Warez\User\Entity\Profile']))
            ->will($this->returnValue($resolver));

        $this->form->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertSame('profile', $this->form->getName());
    }

    protected function setUp()
    {
        $this->form = new Profile();
    }
}

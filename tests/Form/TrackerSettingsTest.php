<?php

namespace Martial\OpenCloudSeedbox\Tests\Form;

use Martial\OpenCloudSeedbox\Form\TrackerSettings;
use Martial\OpenCloudSeedbox\Settings\Entity\TrackerSettingsEntity;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpKernel\Profiler\Profile;

class ProfileTest extends FormTestCase
{
    /**
     * @var Profile
     */
    public $form;

    public function testBuildForm()
    {
        $this
            ->formBuilder
            ->expects($this->exactly(2))
            ->method('add')
            ->withConsecutive(
                [$this->equalTo('username')],
                [
                    $this->equalTo('password'), $this->equalTo(PasswordType::class), $this->equalTo([
                        'required' => false
                    ])
                ]
            )
            ->will($this->returnValue($this->formBuilder));

        $this->getForm()->buildForm($this->formBuilder, []);
    }

    public function testConfigureOptions()
    {
        $this
            ->resolver
            ->expects($this->once())
            ->method('setDefaults')
            ->with($this->equalTo(['data_class' => TrackerSettingsEntity::class]))
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
            $this->form = new TrackerSettings();
        }

        return $this->form;
    }
}

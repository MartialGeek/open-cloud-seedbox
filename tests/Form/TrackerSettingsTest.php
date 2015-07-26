<?php

namespace Martial\OpenCloudSeedbox\Tests\Form;

use Martial\OpenCloudSeedbox\Form\TrackerSettings;
use Symfony\Component\Form\FormTypeInterface;

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
                    $this->equalTo('password'), $this->equalTo('password'), $this->equalTo([
                        'required' => false
                    ])
                ]
            )
            ->will($this->returnValue($this->formBuilder));

        $this->getForm()->buildForm($this->formBuilder, []);
    }

    public function testDefaultOptions()
    {
        $this
            ->resolver
            ->expects($this->once())
            ->method('setDefaults')
            ->with($this->equalTo(['data_class' => '\Martial\OpenCloudSeedbox\Settings\Entity\TrackerSettingsEntity']))
            ->will($this->returnValue($this->resolver));

        $this->getForm()->setDefaultOptions($this->resolver);
    }

    /**
     * Returns the name of the form.
     *
     * @return string
     */
    protected function getFormName()
    {
        return 'tracker_settings';
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

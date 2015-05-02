<?php

namespace Martial\Warez\Tests\Form;

use Martial\Warez\Form\FreeboxSettings;
use Symfony\Component\Form\FormTypeInterface;

class FreeboxSettingsTest extends FormTestCase
{
    public function testBuildForm()
    {
        $this
            ->formBuilder
            ->expects($this->exactly(7))
            ->method('add')
            ->withConsecutive(
                [
                    $this->equalTo('id'),
                    $this->equalTo('hidden')
                ],
                [$this->equalTo('transportHost')],
                [$this->equalTo('transportPort')],
                [$this->equalTo('appId')],
                [$this->equalTo('appName')],
                [$this->equalTo('appVersion')],
                [$this->equalTo('deviceName')]
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
            ->with($this->equalTo(['data_class' => '\Martial\Warez\Settings\Entity\FreeboxSettingsEntity']))
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
        return 'freebox_settings';
    }

    /**
     * Returns an instance of your form.
     *
     * @return FormTypeInterface
     */
    protected function getForm()
    {
        return new FreeboxSettings();
    }
}

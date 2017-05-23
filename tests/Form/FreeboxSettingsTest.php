<?php

namespace Martial\OpenCloudSeedbox\Tests\Form;

use Martial\OpenCloudSeedbox\Form\FreeboxSettings;
use Martial\OpenCloudSeedbox\Settings\Entity\FreeboxSettingsEntity;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
                    $this->equalTo(HiddenType::class)
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

    public function testConfigureOptions()
    {
        $this
            ->resolver
            ->expects($this->once())
            ->method('setDefaults')
            ->with($this->equalTo(['data_class' => FreeboxSettingsEntity::class]))
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
        return new FreeboxSettings();
    }
}

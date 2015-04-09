<?php

namespace Martial\Warez\Tests\Settings;

use Martial\Warez\Settings\SettingsContainer;

class SettingsContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SettingsContainer
     */
    public $container;

    public function testGetRegisteredSettingShouldReturnTheSettingInstance()
    {
        $key = 'tracker';
        $setting = $this->getMock('\Martial\Warez\Settings\SettingsInterface');
        $this->container->register($key, $setting);
        $this->assertSame($setting, $this->container->get($key));
    }

    /**
     * @expectedException \Martial\Warez\Settings\SettingsNotFoundException
     */
    public function testGetUnregisteredSettingShouldThrowAnException()
    {
        $setting = $this->getMock('\Martial\Warez\Settings\SettingsInterface');
        $this->container->register('plop', $setting);
        $this->container->get('paplop');
    }

    public function testGetAllShouldReturnAnArrayOfRegisteredSettings()
    {
        $settings = [
            'first' => $this->getMock('\Martial\Warez\Settings\SettingsInterface'),
            'second' => $this->getMock('\Martial\Warez\Settings\SettingsInterface'),
            'third' => $this->getMock('\Martial\Warez\Settings\SettingsInterface'),
        ];

        foreach ($settings as $key => $setting) {
            $this->container->register($key, $setting);
        }

        $result = $this->container->getAll();
        $this->assertSame($settings, $result);
    }

    protected function setUp()
    {
        $this->container = new SettingsContainer();
    }
}

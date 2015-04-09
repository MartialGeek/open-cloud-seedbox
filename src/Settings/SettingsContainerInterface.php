<?php

namespace Martial\Warez\Settings;


interface SettingsContainerInterface
{
    /**
     * Registers a new setting with the given key.
     *
     * @param string $key
     * @param SettingsInterface $settings
     * @return self
     */
    public function register($key, SettingsInterface $settings);

    /**
     * Returns the setting corresponding to the given key or throws an exception.
     *
     * @param string $key
     * @return SettingsInterface
     * @throws SettingsNotFoundException
     */
    public function get($key);

    /**
     * Retrieves an array of registered settings.
     *
     * @return SettingsInterface[]
     */
    public function getAll();
}

<?php

namespace Martial\Warez\Settings;


class SettingsContainer implements SettingsContainerInterface
{
    /**
     * @var array
     */
    private $settings;

    public function __construct()
    {
        $this->settings = [];
    }

    /**
     * Registers a new setting with the given key.
     *
     * @param string $key
     * @param SettingsInterface $settings
     * @return self
     */
    public function register($key, SettingsInterface $settings)
    {
        $this->settings[$key] = $settings;

        return $this;
    }

    /**
     * Returns the setting corresponding to the given key or throws an exception.
     *
     * @param string $key
     * @return SettingsInterface
     * @throws SettingsNotFoundException
     */
    public function get($key)
    {
        if (!isset($this->settings[$key])) {
            throw new SettingsNotFoundException(
                sprintf('The setting %s was not registered.', $key)
            );
        }

        return $this->settings[$key];
    }

    /**
     * Retrieves an array of registered settings.
     *
     * @return SettingsInterface[]
     */
    public function getAll()
    {
        return $this->settings;
    }
}

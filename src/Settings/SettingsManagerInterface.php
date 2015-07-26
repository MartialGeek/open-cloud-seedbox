<?php

namespace Martial\OpenCloudSeedbox\Settings;

use Martial\OpenCloudSeedbox\User\Entity\User;

interface SettingsManagerInterface
{
    /**
     * @param User $user
     * @return mixed
     */
    public function getSettings(User $user);

    /**
     * @param mixed $settings
     * @param User $user
     */
    public function updateSettings($settings, User $user);

    /**
     * Returns true if the settings are complete.
     *
     * @param mixed $settings
     * @return bool
     */
    public function isComplete($settings);
}

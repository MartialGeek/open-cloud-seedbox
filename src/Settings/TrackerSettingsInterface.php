<?php

namespace Martial\Warez\Settings;

use Martial\Warez\Settings\Entity\TrackerSettingsEntity;

interface TrackerSettingsInterface
{
    /**
     * Encodes the tracker password.
     *
     * @param TrackerSettingsEntity $settings
     */
    public function encodeTrackerPassword(TrackerSettingsEntity $settings);

    /**
     * Decodes the tracker password.
     *
     * @param TrackerSettingsEntity $settings
     */
    public function decodeTrackerPassword(TrackerSettingsEntity $settings);
}

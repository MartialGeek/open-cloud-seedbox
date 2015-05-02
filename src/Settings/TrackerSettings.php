<?php

namespace Martial\Warez\Settings;

use Martial\Warez\Security\EncoderInterface;
use Martial\Warez\Settings\Entity\TrackerSettingsEntity;

class TrackerSettings implements TrackerSettingsInterface
{
    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @param EncoderInterface $encoder
     */
    public function __construct(EncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * Encodes the tracker password.
     *
     * @param TrackerSettingsEntity $settings
     */
    public function encodeTrackerPassword(TrackerSettingsEntity $settings)
    {
        $encodedPassword = $this->encoder->encode($settings->getPassword());
        $settings->setPassword($encodedPassword);
    }

    /**
     * Decodes the tracker password.
     *
     * @param TrackerSettingsEntity $settings
     */
    public function decodeTrackerPassword(TrackerSettingsEntity $settings)
    {
        $clearPassword = $this->encoder->decode($settings->getPassword());
        $settings->setPassword($clearPassword);
    }
}

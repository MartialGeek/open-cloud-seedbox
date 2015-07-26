<?php

namespace Martial\OpenCloudSeedbox\Settings;

use Doctrine\ORM\EntityManagerInterface;
use Martial\OpenCloudSeedbox\Security\EncoderInterface;
use Martial\OpenCloudSeedbox\Settings\Entity\TrackerSettingsEntity;
use Martial\OpenCloudSeedbox\User\Entity\User;

class TrackerSettings
{
    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @param EncoderInterface $encoder
     * @param EntityManagerInterface $em
     */
    public function __construct(EncoderInterface $encoder, EntityManagerInterface $em)
    {
        $this->encoder = $encoder;
        $this->em = $em;
    }

    /**
     * @param User $user
     * @return TrackerSettingsEntity
     */
    public function getSettings(User $user)
    {
        $settings = $this
            ->em
            ->getRepository('\Martial\OpenCloudSeedbox\Settings\Entity\TrackerSettingsEntity')
            ->findOneBy(['user' => $user]);

        if (is_null($settings)) {
            $settings = new TrackerSettingsEntity();
            $settings->setUser($user);
        }

        $this->decodeTrackerPassword($settings);

        return $settings;
    }

    /**
     * @param TrackerSettingsEntity $settings
     * @param User $user
     */
    public function updateSettings(TrackerSettingsEntity $settings, User $user)
    {
        $currentSettings = $this->getSettings($user);
        $newPassword = $settings->getPassword();

        if ($currentSettings->getPassword() != $newPassword && !is_null($newPassword)) {
            $currentSettings->setPassword($newPassword);
            $this->encodeTrackerPassword($currentSettings);
        }

        $currentSettings->setUsername($settings->getUsername());

        if (is_null($currentSettings->getId())) {
            $this->em->persist($currentSettings);
        }

        $this->em->flush();
    }

    /**
     * Encodes the tracker password.
     *
     * @param TrackerSettingsEntity $settings
     */
    private function encodeTrackerPassword(TrackerSettingsEntity $settings)
    {
        $encodedPassword = $this->encoder->encode($settings->getPassword());
        $settings->setPassword($encodedPassword);
    }

    /**
     * Decodes the tracker password.
     *
     * @param TrackerSettingsEntity $settings
     */
    private function decodeTrackerPassword(TrackerSettingsEntity $settings)
    {
        $clearPassword = $this->encoder->decode($settings->getPassword());
        $settings->setPassword($clearPassword);
    }
}

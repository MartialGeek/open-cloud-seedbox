<?php

namespace Martial\OpenCloudSeedbox\Settings;

use Doctrine\ORM\EntityManagerInterface;
use Martial\OpenCloudSeedbox\Settings\Entity\FreeboxSettingsEntity;
use Martial\OpenCloudSeedbox\User\Entity\User;

class FreeboxSettings
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param User $user
     * @return FreeboxSettingsEntity
     */
    public function getSettings(User $user)
    {
        $settings = $this
            ->em
            ->getRepository('\Martial\OpenCloudSeedbox\Settings\Entity\FreeboxSettingsEntity')
            ->findOneBy(['user' => $user]);

        if (is_null($settings)) {
            $settings = new FreeboxSettingsEntity();
            $settings->setUser($user);
        }

        return $settings;
    }

    /**
     * @param FreeboxSettingsEntity $settings
     * @param User $user
     */
    public function updateSettings(FreeboxSettingsEntity $settings, User $user)
    {
        $currentSettings = $this->getSettings($user);

        $currentSettings
            ->setSessionToken($settings->getSessionToken())
            ->setAppId($settings->getAppId())
            ->setAppName($settings->getAppName())
            ->setAppVersion($settings->getAppVersion())
            ->setDeviceName($settings->getDeviceName())
            ->setTransportHost($settings->getTransportHost())
            ->setTransportPort($settings->getTransportPort());

        if (is_null($currentSettings->getId())) {
            $this->em->persist($currentSettings);
        }

        $this->em->flush();
    }
}

<?php

namespace Martial\Warez\Settings;

use Doctrine\ORM\EntityManagerInterface;
use Martial\Warez\Settings\Entity\FreeboxSettingsEntity;
use Martial\Warez\User\Entity\User;

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
            ->getRepository('\Martial\Warez\Settings\Entity\FreeboxSettingsEntity')
            ->findOneBy(['user' => $user]);

        if (is_null($settings)) {
            $settings = new FreeboxSettingsEntity();
            $settings->setUser($user);
        }

        return $settings;
    }

    public function updateSettings(FreeboxSettingsEntity $settings, User $user)
    {
        $settings->setUser($user);
        $this->em->persist($settings);
        $this->em->flush();
    }
}

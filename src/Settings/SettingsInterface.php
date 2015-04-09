<?php

namespace Martial\Warez\Settings;

use Martial\Warez\User\Entity\User;
use Symfony\Component\Form\FormInterface;

interface SettingsInterface
{
    /**
     * Updates the settings of the user.
     *
     * @param User $user
     * @param mixed $settings
     * @throws SettingsUpdatingException
     */
    public function updateSettings(User $user, $settings);

    /**
     * Retrieves the settings of the user.
     *
     * @param User $user
     * @return mixed
     */
    public function getSettings(User $user);

    /**
     * Renders the view for managing the settings.
     *
     * @param User $user
     * @param FormInterface $form
     * @return string
     */
    public function getView(User $user, FormInterface $form = null);
}

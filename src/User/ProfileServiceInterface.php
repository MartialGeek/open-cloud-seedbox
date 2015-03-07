<?php

namespace Martial\Warez\User;

use Martial\Warez\User\Entity\Profile;

interface ProfileServiceInterface
{
    /**
     * Encodes the tracker password.
     *
     * @param Profile $profile
     */
    public function encodeTrackerPassword(Profile $profile);

    /**
     * Decodes the tracker password.
     *
     * @param Profile $profile
     */
    public function decodeTrackerPassword(Profile $profile);
}

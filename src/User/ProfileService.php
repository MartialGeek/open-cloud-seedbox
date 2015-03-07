<?php

namespace Martial\Warez\User;

use Martial\Warez\Security\EncoderInterface;
use Martial\Warez\User\Entity\Profile;

class ProfileService implements ProfileServiceInterface
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
     * @param Profile $profile
     */
    public function encodeTrackerPassword(Profile $profile)
    {
        $encodedPassword = $this->encoder->encode($profile->getT411Password());
        $profile->setT411Password($encodedPassword);
    }

    /**
     * Decodes the tracker password.
     *
     * @param Profile $profile
     */
    public function decodeTrackerPassword(Profile $profile)
    {
        $clearPassword = $this->encoder->decode($profile->getT411Password());
        $profile->setT411Password($clearPassword);
    }
}

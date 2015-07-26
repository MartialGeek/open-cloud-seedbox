<?php

namespace Martial\OpenCloudSeedbox\Security;


interface EncoderInterface
{
    /**
     * Returns an encrypted string from the provided data.
     *
     * @param string $clearData
     * @return string
     */
    public function encode($clearData);

    /**
     * Returns a decrypted string from the provided data.
     *
     * @param string $encodedData
     * @return string
     */
    public function decode($encodedData);
}

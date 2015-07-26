<?php

namespace Martial\OpenCloudSeedbox\Security;

use Pikirasa\RSA;

class OpenSSLEncoder implements EncoderInterface
{
    /**
     * @var RSA
     */
    private $rsa;

    /**
     * @param RSA $rsa
     */
    public function __construct(RSA $rsa)
    {
        $this->rsa = $rsa;
    }

    /**
     * Returns an encrypted string from the provided data.
     *
     * @param string $clearData
     * @return string
     */
    public function encode($clearData)
    {
        return $this->rsa->encrypt($clearData);
    }

    /**
     * Returns a decrypted string from the provided data.
     *
     * @param string $encodedData
     * @return string
     */
    public function decode($encodedData)
    {
        return $this->rsa->decrypt($encodedData);
    }
}

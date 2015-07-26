<?php

namespace Martial\OpenCloudSeedbox\Security;


class OpenSSLEncoder implements EncoderInterface
{
    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $cypherMethod;

    /**
     * @var string
     */
    private $salt;

    /**
     * @param string $password
     * @param string $salt
     * @param string $cypherMethod
     */
    public function __construct($password, $salt, $cypherMethod = 'aes-256-ecb')
    {
        $this->password = $password;
        $this->salt = $salt;
        $this->cypherMethod = $cypherMethod;
    }

    /**
     * Returns an encrypted string from the provided data.
     *
     * @param string $clearData
     * @return string
     */
    public function encode($clearData)
    {
        return openssl_encrypt($clearData, $this->cypherMethod, $this->password, false, $this->salt);
    }

    /**
     * Returns a decrypted string from the provided data.
     *
     * @param string $encodedData
     * @return string
     */
    public function decode($encodedData)
    {
        return openssl_decrypt($encodedData, $this->cypherMethod, $this->password, false, $this->salt);
    }
}

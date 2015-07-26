<?php

namespace Martial\OpenCloudSeedbox\Security;


class BlowfishHashPassword implements PasswordHashInterface
{
    /**
     * Returns a hashed password from a string.
     *
     * @param string $password
     * @return string
     */
    public function generateHash($password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Checks if a clear password is corresponding to a hash.
     *
     * @param string $clearPassword
     * @param string $hashedPassword
     * @return bool
     */
    public function isValid($clearPassword, $hashedPassword)
    {
        return password_verify($clearPassword, $hashedPassword);
    }
}

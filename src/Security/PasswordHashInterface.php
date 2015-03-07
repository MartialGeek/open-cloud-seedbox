<?php

namespace Martial\Warez\Security;


interface PasswordHashInterface
{
    /**
     * Returns a hashed password from a string.
     *
     * @param string $password
     * @return string
     */
    public function generateHash($password);

    /**
     * Checks if a clear password is corresponding to a hash.
     *
     * @param string $clearPassword
     * @param string $hashedPassword
     * @return bool
     */
    public function isValid($clearPassword, $hashedPassword);
}

<?php

namespace Martial\Warez\Security;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Martial\Warez\User\Entity\User;
use Martial\Warez\User\Repository\UserRepositoryInterface;

class AuthenticationProvider implements AuthenticationProviderInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Authenticates a user by its email and a non-hashed password or throw an exception.
     *
     * @param string $email
     * @param string $password
     * @return User
     * @throws BadCredentialsException
     */
    public function authenticateByEmail($email, $password)
    {
        try {
            $user = $this->getRepository()->findUserByEmailAndPassword(
                $email,
                $this->generatePasswordHash($password)
            );

            return $user;
        } catch (NoResultException $e) {
            throw new BadCredentialsException('', 0, $e);
        }
    }

    /**
     * Generates a hash from the given password.
     *
     * @param string $password
     * @return string
     */
    public function generatePasswordHash($password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * @return UserRepositoryInterface
     */
    protected function getRepository()
    {
        return $this
            ->em
            ->getRepository('\Martial\Warez\User\Entity\User');
    }
}

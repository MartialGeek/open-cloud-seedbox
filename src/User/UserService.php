<?php

namespace Martial\Warez\User;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Martial\Warez\Security\AuthenticationProviderInterface;
use Martial\Warez\User\Entity\User;

class UserService implements UserServiceInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var AuthenticationProviderInterface
     */
    private $authentication;

    /**
     * @param EntityManager $em
     * @param AuthenticationProviderInterface $authentication
     */
    public function __construct(EntityManager $em, AuthenticationProviderInterface $authentication)
    {
        $this->em = $em;
        $this->authentication = $authentication;
    }

    /**
     * Registers a new user.
     *
     * @param User $user
     */
    public function register(User $user)
    {
        $hashedPassword = $this->authentication->generatePasswordHash($user->getPassword());
        $user->setPassword($hashedPassword);
        $this->em->persist($user);
        $this->em->flush();

    }

    /**
     * @param User $user
     */
    public function unregister(User $user)
    {
        $this->em->remove($user);
        $this->em->flush();
    }

    /**
     * Updates the account of a user.
     *
     * @param User $user
     */
    public function updateAccount(User $user)
    {
        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * Authenticates a user with its email.
     *
     * @param string $email
     * @param string $password
     * @return User
     * @throws BadCredentialsException
     */
    public function authenticateByEmail($email, $password)
    {
        $hashedPassword = $this->authentication->generatePasswordHash($password);

        try {
            return $this
                ->em
                ->getRepository('\Martial\Warez\User\Entity\User')
                ->findUserByEmailAndPassword($email, $hashedPassword);
        } catch (NoResultException $e) {
            throw new BadCredentialsException();
        }
    }
}

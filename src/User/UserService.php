<?php

namespace Martial\OpenCloudSeedbox\User;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Martial\OpenCloudSeedbox\Security\AuthenticationProviderInterface;
use Martial\OpenCloudSeedbox\Security\BadCredentialsException;
use Martial\OpenCloudSeedbox\Security\PasswordHashInterface;
use Martial\OpenCloudSeedbox\User\Entity\User;
use Martial\OpenCloudSeedbox\User\Repository\UserRepositoryInterface;

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
     * @var PasswordHashInterface
     */
    private $passwordHash;

    /**
     * @param EntityManager $em
     * @param AuthenticationProviderInterface $authentication
     * @param PasswordHashInterface $passwordHash
     */
    public function __construct(
        EntityManager $em,
        AuthenticationProviderInterface $authentication,
        PasswordHashInterface $passwordHash
    ) {
        $this->em = $em;
        $this->authentication = $authentication;
        $this->passwordHash = $passwordHash;
    }

    /**
     * Registers a new user.
     *
     * @param User $user
     * @throws EmailAlreadyExistsException
     * @throws UsernameAlreadyExistsException
     */
    public function register(User $user)
    {
        if (count($this->getRepository()->findBy([
            'email' => $user->getEmail()
        ]))) {
            throw new EmailAlreadyExistsException();
        }

        if (count($this->getRepository()->findBy([
            'username' => $user->getUsername()
        ]))) {
            throw new UsernameAlreadyExistsException();
        }

        $hashedPassword = $this->passwordHash->generateHash($user->getPassword());
        $user->setPassword($hashedPassword);
        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * Unregisters a user.
     *
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
     * @throws UserNotFoundException
     */
    public function authenticateByEmail($email, $password)
    {
        try {
            $user = $this->getRepository()->findUserByEmail($email);
        } catch (NoResultException $e) {
            throw new UserNotFoundException();
        }

        if (!$this->authentication->hasValidCredentials($user->getPassword(), $password)) {
            throw new BadCredentialsException();
        }

        return $user;
    }

    /**
     * Finds a user by its ID.
     *
     * @param int $userId
     * @return User
     * @throws UserNotFoundException
     */
    public function find($userId)
    {
        $user = $this->getRepository()->find($userId);

        if (is_null($user)) {
            throw new UserNotFoundException();
        }

        return $user;
    }

    /**
     * @return UserRepositoryInterface
     */
    protected function getRepository()
    {
        return $this->em->getRepository('\Martial\OpenCloudSeedbox\User\Entity\User');
    }
}

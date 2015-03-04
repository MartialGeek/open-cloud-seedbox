<?php

namespace Martial\Warez\User;

use Doctrine\ORM\EntityManager;
use Martial\Warez\Security\AuthenticationProviderInterface;
use Martial\Warez\User\Entity\User;
use Martial\Warez\User\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

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

        $hashedPassword = $this->authentication->generatePasswordHash($user->getPassword());
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
        return $this->authentication->authenticateByEmail($email, $password);
    }

    /**
     * Finds a user by its ID.
     *
     * @param int $userId
     * @return mixed
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
        return $this->em->getRepository('\Martial\Warez\User\Entity\User');
    }
}

<?php

namespace Martial\Warez\User;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Martial\Warez\Security\AuthenticationProviderInterface;
use Martial\Warez\Security\BadCredentialsException;
use Martial\Warez\Security\PasswordHashInterface;
use Martial\Warez\User\Entity\Profile;
use Martial\Warez\User\Entity\User;
use Martial\Warez\User\Repository\UserRepositoryInterface;

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
     * @var ProfileServiceInterface
     */
    private $profileService;

    /**
     * @param EntityManager $em
     * @param AuthenticationProviderInterface $authentication
     * @param PasswordHashInterface $passwordHash
     * @param ProfileServiceInterface $profileService
     */
    public function __construct(
        EntityManager $em,
        AuthenticationProviderInterface $authentication,
        PasswordHashInterface $passwordHash,
        ProfileServiceInterface $profileService
    ) {
        $this->em = $em;
        $this->authentication = $authentication;
        $this->passwordHash = $passwordHash;
        $this->profileService = $profileService;
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

        if (!$this->authentication->hasValidCredentials($user, $password)) {
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
     * Updates the profile of a user.
     *
     * @param int $userId
     * @param Profile $profile
     * @return User
     */
    public function updateProfile($userId, Profile $profile)
    {
        $user = $this->find($userId);
        $currentProfile = is_null($user->getProfile()) ? new Profile() : $user->getProfile();
        $currentTrackerPassword = $currentProfile->getTrackerPassword();
        $newTrackerPassword = $profile->getTrackerPassword();

        if ($currentTrackerPassword != $newTrackerPassword && !is_null($newTrackerPassword)) {
            $currentProfile->setTrackerPassword($newTrackerPassword);
            $this->profileService->encodeTrackerPassword($currentProfile);
        }

        $currentProfile->setTrackerUsername($profile->getTrackerUsername());

        $this->em->persist($currentProfile);
        $this->em->flush();
    }

    /**
     * @return UserRepositoryInterface
     */
    protected function getRepository()
    {
        return $this->em->getRepository('\Martial\Warez\User\Entity\User');
    }
}

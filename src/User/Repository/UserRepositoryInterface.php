<?php

namespace Martial\OpenCloudSeedbox\User\Repository;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\NoResultException;
use Martial\OpenCloudSeedbox\User\Entity\User;

interface UserRepositoryInterface extends ObjectRepository, Selectable
{
    /**
     * Finds a user by its email and its password.
     *
     * @param string $email
     * @param string $password
     * @return User
     * @throws NoResultException
     */
    public function findUserByEmailAndPassword($email, $password);

    /**
     * Finds a user by its email.
     *
     * @param string $email
     * @return User
     * @throws NoResultException
     */
    public function findUserByEmail($email);
}

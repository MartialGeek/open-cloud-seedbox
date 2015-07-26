<?php

namespace Martial\OpenCloudSeedbox\Security;

use Doctrine\ORM\EntityManagerInterface;
use Martial\OpenCloudSeedbox\User\Entity\User;

class CookieTokenizer implements CookieTokenizerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Generates and stores a token for the given user.
     *
     * @param User $user
     * @return CookieTokenInterface
     */
    public function generateAndStoreToken(User $user)
    {
        $tokenId = password_hash($user->getUsername(), PASSWORD_BCRYPT);
        $tokenHash = password_hash($user->getPassword(), PASSWORD_BCRYPT);

        $user->setCookieTokenId($tokenId);
        $user->setCookieTokenHash($tokenHash);
        $this->em->flush();

        return new CookieToken($tokenId, $tokenHash, $user);
    }

    /**
     * Retrieves the token or throws an exception.
     *
     * @param string $id
     * @return CookieTokenInterface
     * @throws CookieTokenNotFoundException
     */
    public function findToken($id)
    {
        /**
         * @var User $user
         */
        $user = $this->em->getRepository('\Martial\OpenCloudSeedbox\User\Entity\User')->findOneBy(['cookieTokenId' => $id]);

        if (is_null($user)) {
            throw new CookieTokenNotFoundException();
        }

        return new CookieToken($user->getCookieTokenId(), $user->getCookieTokenHash(), $user);
    }
}

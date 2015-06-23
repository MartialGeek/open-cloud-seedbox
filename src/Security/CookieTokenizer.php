<?php

namespace Martial\Warez\Security;

use Doctrine\ORM\EntityManagerInterface;
use Martial\Warez\User\Entity\User;

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
        $tokenId = uniqid($user->getUsername());
        $tokenHash = sha1($user->getPassword());

        $user->setCookieTokenId($tokenId);
        $user->setCookieTokenHash($tokenHash);
        $this->em->flush();

        return new CookieToken($tokenId, $tokenHash);
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
        $user = $this->em->getRepository('\Martial\Warez\User\Entity\User')->findOneBy(['cookie_token_id' => $id]);

        if (is_null($user)) {
            throw new CookieTokenNotFoundException();
        }

        return new CookieToken($user->getCookieTokenId(), $user->getCookieTokenHash());
    }
}

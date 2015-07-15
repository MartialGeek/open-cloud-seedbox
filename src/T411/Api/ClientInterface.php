<?php

namespace Martial\Warez\T411\Api;

use Martial\Warez\T411\Api\Authentication\AccountDisabledException;
use Martial\Warez\T411\Api\Authentication\AccountNotConfirmedException;
use Martial\Warez\T411\Api\Authentication\AuthenticationException;
use Martial\Warez\T411\Api\Authentication\AuthorizationLimitReachedException;
use Martial\Warez\T411\Api\Authentication\TokenInterface;
use Martial\Warez\T411\Api\Authentication\UserNotFoundException;
use Martial\Warez\T411\Api\Authentication\WrongPasswordException;
use Martial\Warez\T411\Api\Category\CategoryInterface;
use Martial\Warez\T411\Api\Torrent\TorrentSearchResultInterface;
use Symfony\Component\HttpFoundation\File\File;

interface ClientInterface
{
    /**
     * Authenticates a user and returns an authentication token.
     *
     * @param string $username
     * @param string $password
     * @return TokenInterface
     * @throws AccountDisabledException
     * @throws AccountNotConfirmedException
     * @throws AuthenticationException
     * @throws AuthorizationLimitReachedException
     * @throws UserNotFoundException
     * @throws WrongPasswordException
     */
    public function authenticate($username, $password);

    /**
     * Retrieves the list of the categories.
     *
     * @param TokenInterface $token
     * @return CategoryInterface[]
     */
    public function getCategories(TokenInterface $token);

    /**
     * Retrieves a list of torrents matching the query parameters. Supported parameters are:
     * <ul>
     * <li>terms</li>
     * <li>category_id</li>
     * <li>offset</li>
     * <li>limit</li>
     * </ul>
     *
     * @param TokenInterface $token
     * @param array $queryParams
     * @return TorrentSearchResultInterface[]
     */
    public function search(TokenInterface $token, array $queryParams);

    /**
     * Downloads a torrent file.
     *
     * @param TokenInterface $token
     * @param int $torrentId
     * @return File
     */
    public function download(TokenInterface $token, $torrentId);
}

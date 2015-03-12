<?php

namespace Martial\Warez\T411\Api;

use GuzzleHttp\ClientInterface as HttpClientInterface;
use Martial\Warez\T411\Api\Authentication\AccountDisabledException;
use Martial\Warez\T411\Api\Authentication\AccountNotConfirmedException;
use Martial\Warez\T411\Api\Authentication\AuthenticationException;
use Martial\Warez\T411\Api\Authentication\AuthorizationLimitReachedException;
use Martial\Warez\T411\Api\Authentication\Token;
use Martial\Warez\T411\Api\Authentication\TokenInterface;
use Martial\Warez\T411\Api\Authentication\UserNotFoundException;
use Martial\Warez\T411\Api\Authentication\WrongPasswordException;
use Martial\Warez\T411\Api\Category\CategoryInterface;
use Martial\Warez\T411\Api\Data\DataTransformerInterface;
use Martial\Warez\T411\Api\Torrent\TorrentSearchResultInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class Client implements ClientInterface
{
    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var DataTransformerInterface
     */
    private $dataTransformer;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var array
     */
    private $config;

    /**
     * @param HttpClientInterface $httpClient
     * @param DataTransformerInterface $dataTransformer
     * @param Filesystem $fs
     * @param array $config
     */
    public function __construct(
        HttpClientInterface $httpClient,
        DataTransformerInterface $dataTransformer,
        Filesystem $fs,
        array $config
    ) {
        $this->httpClient = $httpClient;
        $this->dataTransformer = $dataTransformer;
        $this->fs = $fs;
        $this->config = $config;
    }

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
    public function authenticate($username, $password)
    {
        $response = $this->httpClient->post('/auth', [
            'body' => [
                'username' => $username,
                'password' => $password
            ]
        ])->json();

        if (isset($response['error'])) {
            switch ($response['code']) {
                case 101:
                    throw new UserNotFoundException();
                case 102:
                    throw new AccountNotConfirmedException();
                case 103:
                case 104:
                case 105:
                    throw new AccountDisabledException();
                case 106:
                    throw new AuthorizationLimitReachedException();
                case 107:
                    throw new WrongPasswordException();
                default:
                    throw new AuthenticationException(
                        'An error occurred during the authentication'
                    );
            }
        }

        $token = new Token();
        $token->setUid($response['uid']);
        $token->setToken($response['token']);

        return $token;
    }

    /**
     * Retrieves the list of the categories.
     *
     * @param TokenInterface $token
     * @return CategoryInterface[]
     */
    public function getCategories(TokenInterface $token)
    {
        $response = $this->httpClient->get(
            '/categories/tree',
            [
                'headers' => ['Authorization' => $token->getToken()]
            ]
        )->json();

        return $this->dataTransformer->extractCategoriesFromApiResponse($response);
    }

    /**
     * Retrieves a list of torrents matching the searched query.
     *
     * @param TokenInterface $token
     * @param string $query
     * @param int $offset
     * @param int $limit
     * @return TorrentSearchResultInterface
     */
    public function search(TokenInterface $token, $query, $offset = null, $limit = null)
    {
        if (!is_null($offset)) {
            $query .= '&offset=' . $offset;
        }

        if (!is_null($limit)) {
            $query .= '&limit=' . $limit;
        }

        $response = $this->httpClient->get(
            '/torrents/search/' . $query,
            [
                'headers' => ['Authorization' => $token->getToken()]
            ]
        )->json();

        return $this->dataTransformer->extractTorrentsFromApiResponse($response);
    }

    /**
     * Downloads a torrent file.
     *
     * @param TokenInterface $token
     * @param int $torrentId
     * @return File
     */
    public function download(TokenInterface $token, $torrentId)
    {
        $response = $this->httpClient->get(
            '/torrents/download/' . $torrentId,
            [
                'headers' => ['Authorization' => $token->getToken()]
            ]
        );

        $filename = $this->config['torrent_files_path'] . '/' . uniqid() . '.torrent';
        $this->fs->dumpFile($filename, $response, 0660);

        return new File($filename, false);
    }
}

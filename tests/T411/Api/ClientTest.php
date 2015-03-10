<?php

namespace Martial\Warez\Tests\T411\Api;

use Martial\Warez\T411\Api\Category\Category;
use Martial\Warez\T411\Api\Client;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    const AUTH_USER_NOT_FOUND = 'user_not_found';
    const AUTH_ACCOUNT_NOT_CONFIRMED = 'account_not_confirmed';
    const AUTH_AUTHORIZATION_LIMIT_REACHED = 'authorization_limit_reached';
    const AUTH_ACCOUNT_DISABLED = 'account_disabled';
    const AUTH_ACCOUNT_DISABLED_DUE_TO_CHEATS = 'account_disabled_due_to_cheats';
    const AUTH_ACCOUNT_DISABLED_BY_ADMIN = 'account_disabled_by_admin';
    const AUTH_WRONG_PASSWORD = 'wrong_password';
    const AUTH_UNKNOWN_ERROR = 'unknown_error';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $httpClient;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $dataTransformer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $apiResponse;

    /**
     * @var Client
     */
    public $client;

    public function testAuthenticateShouldReturnATokenInCaseOfSuccess()
    {
        $this->authenticate();
    }

    /**
     * @expectedException \Martial\Warez\T411\Api\Authentication\UserNotFoundException
     */
    public function testAuthenticateShouldThrowAnExceptionWhenTheUserDoesNotExist()
    {
        $this->authenticate(self::AUTH_USER_NOT_FOUND);
    }

    /**
     * @expectedException \Martial\Warez\T411\Api\Authentication\AccountNotConfirmedException
     */
    public function testAuthenticateShouldThrowAnExceptionOnNotConfirmedAccount()
    {
        $this->authenticate(self::AUTH_ACCOUNT_NOT_CONFIRMED);
    }

    /**
     * @expectedException \Martial\Warez\T411\Api\Authentication\AuthorizationLimitReachedException
     */
    public function testAuthenticateShouldThrowAnExceptionWhenTheAuthorizationLimitIsReached()
    {
        $this->authenticate(self::AUTH_AUTHORIZATION_LIMIT_REACHED);
    }

    /**
     * @expectedException \Martial\Warez\T411\Api\Authentication\AccountDisabledException
     */
    public function testAuthenticateShouldThrowAnExceptionWhenTheAccountIsDisabled()
    {
        $this->authenticate(self::AUTH_ACCOUNT_DISABLED);
    }

    /**
     * @expectedException \Martial\Warez\T411\Api\Authentication\AccountDisabledException
     */
    public function testAuthenticateShouldThrowAnExceptionWhenTheAccountIsDisabledByAnAdmin()
    {
        $this->authenticate(self::AUTH_ACCOUNT_DISABLED_BY_ADMIN);
    }

    /**
     * @expectedException \Martial\Warez\T411\Api\Authentication\AccountDisabledException
     */
    public function testAuthenticateShouldThrowAnExceptionWhenTheAccountIsDisabledBecauseOfCheats()
    {
        $this->authenticate(self::AUTH_ACCOUNT_DISABLED_DUE_TO_CHEATS);
    }

    /**
     * @expectedException \Martial\Warez\T411\Api\Authentication\WrongPasswordException
     */
    public function testAuthenticateShouldThrowAnExceptionWhenThePasswordIsWrong()
    {
        $this->authenticate(self::AUTH_WRONG_PASSWORD);
    }

    /**
     * @expectedException \Martial\Warez\T411\Api\Authentication\AuthenticationException
     */
    public function testAuthenticateShouldTrowAnExceptionOnUnknownError()
    {
        $this->authenticate(self::AUTH_UNKNOWN_ERROR);
    }

    public function testCategoriesShouldReturnAListOfCategories()
    {
        $this->getCategories();
    }

    public function testSearchShouldReturnATorrentSearchResultInterface()
    {
        $this->search();
    }

    protected function setUp()
    {
        $this->httpClient = $this->getMock('\GuzzleHttp\ClientInterface');
        $this->apiResponse = $this->getMock('\GuzzleHttp\Message\ResponseInterface');
        $this->dataTransformer = $this->getMock('\Martial\Warez\T411\Api\Data\DataTransformerInterface');
        $this->client = new Client($this->httpClient, $this->dataTransformer);
    }

    /**
     * Simulates an authentication through the T411 API.
     *
     * @param string $errorType
     * @throws \Martial\Warez\T411\Api\Authentication\AccountDisabledException
     * @throws \Martial\Warez\T411\Api\Authentication\AccountNotConfirmedException
     * @throws \Martial\Warez\T411\Api\Authentication\AuthenticationException
     * @throws \Martial\Warez\T411\Api\Authentication\AuthorizationLimitReachedException
     * @throws \Martial\Warez\T411\Api\Authentication\UserNotFoundException
     * @throws \Martial\Warez\T411\Api\Authentication\WrongPasswordException
     */
    protected function authenticate($errorType = '')
    {
        $username = 'SuperWarezMan';
        $password = 'p@ssw0rD';

        switch ($errorType) {
            case self::AUTH_USER_NOT_FOUND:
                $jsonToken = '{"error":"User not found","code":101}';
                break;
            case self::AUTH_ACCOUNT_NOT_CONFIRMED:
                $jsonToken = '{"error":"Account not confirmed","code":102}';
                break;
            case self::AUTH_ACCOUNT_DISABLED:
                $jsonToken = '{"error":"Account disabled","code":103}';
                break;
            case self::AUTH_ACCOUNT_DISABLED_DUE_TO_CHEATS:
                $jsonToken = '{"error":"Account disabled due to hacks and cheats","code":104}';
                break;
            case self::AUTH_ACCOUNT_DISABLED_BY_ADMIN:
                $jsonToken = '{"error":"Account disabled by an administrator","code":105}';
                break;
            case self::AUTH_AUTHORIZATION_LIMIT_REACHED:
                $jsonToken = '{"error":"Authorization limit reached","code":106}';
                break;
            case self::AUTH_WRONG_PASSWORD:
                $jsonToken = '{"error":"Wrong password","code":107}';
                break;
            case self::AUTH_UNKNOWN_ERROR:
                $jsonToken = '{"error":"You are not human!","code":142}';
                break;
            default:
                $jsonToken = '{"uid":"98760954","token":"98760954:31:c18d164416c6affb50b41e233484a278"}';
        }

        $this->requestApi('post', '/auth', $this->equalTo([
            'body' => [
                'username' => $username,
                'password' => $password
            ]
        ]));

        $this->decodeResponse(json_decode($jsonToken, true));
        $token = $this->client->authenticate($username, $password);

        if (is_null($errorType)) {
            $this->assertInstanceOf('\Martial\Warez\T411\Api\Authentication\TokenInterface', $token);
        }
    }

    /**
     * Simulates a request to retrieve the list of the categories.
     */
    protected function getCategories()
    {
        $token = $this->getMock('\Martial\Warez\T411\Api\Authentication\TokenInterface');
        $tokenStr = uniqid();
        $apiResponse = include __DIR__ . '/mockCategoriesResponse.php';

        $this->extractToken($token, $tokenStr);

        $this->requestApi('get', '/categories/tree', $this->equalTo([
            'headers' => ['Authorization' => $tokenStr]
        ]));

        $this->decodeResponse($apiResponse);
        $transformedData = [
            new Category(),
            new Category()
        ];
        $this->transformData('extractCategoriesFromApiResponse', $apiResponse, $transformedData);
        $result = $this->client->getCategories($token);
        $this->assertSame($transformedData, $result);
    }

    protected function search()
    {
        $token = $this->getMock('\Martial\Warez\T411\Api\Authentication\TokenInterface');
        $tokenStr = uniqid();
        $keyWord = 'avatar';
        $offset = 2;
        $limit = 20;
        $apiResponse = require __DIR__ . '/mockTorrentsResponse.php';

        $this->extractToken($token, $tokenStr);
        $query = $keyWord . '&offset=' . $offset . '&limit=' . $limit;
        $this->requestApi('get', '/torrents/search/' . $query, $this->equalTo([
            'headers' => ['Authorization' => $tokenStr]
        ]));

        $this->decodeResponse($apiResponse);
        $transformedData = $this->getMock('\Martial\Warez\T411\Api\Torrent\TorrentSearchResultInterface');
        $this->transformData('extractTorrentsFromApiResponse', $apiResponse, $transformedData);
        $torrentSearchResult = $this->client->search($token, $keyWord, $offset, $limit);
        $this->assertSame($transformedData, $torrentSearchResult);
    }

    /**
     * Simulates a request to the API.
     *
     * @param string $method
     * @param string $uri
     * @param \PHPUnit_Framework_Constraint $constraintParams
     */
    private function requestApi($method, $uri, \PHPUnit_Framework_Constraint $constraintParams)
    {
        $this
            ->httpClient
            ->expects($this->once())
            ->method($method)
            ->with($this->equalTo($uri), $constraintParams)
            ->will($this->returnValue($this->apiResponse));
    }

    /**
     * Simulates the call to the 'json' method of the response.
     *
     * @param array $returnedValue
     */
    private function decodeResponse(array $returnedValue)
    {
        $this->apiResponse
            ->expects($this->once())
            ->method('json')
            ->will($this->returnValue($returnedValue));
    }

    /**
     * Extracts the string part of the token.
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $token
     * @param string $tokenString
     */
    private function extractToken(\PHPUnit_Framework_MockObject_MockObject $token, $tokenString)
    {
        $token
            ->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($tokenString));
    }

    /**
     * Simulates a data transformation.
     *
     * @param string $method
     * @param array $apiResponse
     * @param mixed $returnedValue
     */
    private function transformData($method, $apiResponse, $returnedValue)
    {
        $this
            ->dataTransformer
            ->expects($this->once())
            ->method($method)
            ->with($this->equalTo($apiResponse))
            ->will($this->returnValue($returnedValue));
    }
}

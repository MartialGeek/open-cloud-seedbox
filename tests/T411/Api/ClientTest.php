<?php

namespace Martial\Warez\Tests\T411\Api;

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

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $httpClient;

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

    protected function setUp()
    {
        $this->httpClient = $this->getMock('\GuzzleHttp\ClientInterface');
        $this->client = new Client($this->httpClient);
    }

    protected function authenticate($errorType = null)
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
            default:
                $jsonToken = '{"uid":"98760954","token":"98760954:31:c18d164416c6affb50b41e233484a278"}';
        }

        $arrayToken = json_decode($jsonToken, true);
        $response = $this->getMock('\GuzzleHttp\Message\ResponseInterface');

        $this
            ->httpClient
            ->expects($this->once())
            ->method('post')
            ->with($this->equalTo('/auth'), $this->equalTo([
                'body' => [
                    'username' => $username,
                    'password' => $password
                ]
            ]))
            ->will($this->returnValue($response));

        $response
            ->expects($this->once())
            ->method('json')
            ->will($this->returnValue($arrayToken));

        $token = $this->client->authenticate($username, $password);

        if (is_null($errorType)) {
            $this->assertInstanceOf('\Martial\Warez\T411\Api\Authentication\TokenInterface', $token);
        }
    }
}

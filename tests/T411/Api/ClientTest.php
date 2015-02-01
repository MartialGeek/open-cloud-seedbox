<?php

namespace Martial\Warez\Tests\T411\Api;

use Martial\Warez\T411\Api\Client;

class ClientTest extends \PHPUnit_Framework_TestCase
{
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
        $username = 'SuperWarezMan';
        $password = 'p@ssw0rD';
        $jsonToken = '{"uid":"98760954","token":"98760954:31:c18d164416c6affb50b41e233484a278"}';
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
        $this->assertInstanceOf('\Martial\Warez\T411\Api\Authentication\TokenInterface', $token);
    }

    protected function setUp()
    {
        $this->httpClient = $this->getMock('\GuzzleHttp\ClientInterface');
        $this->client = new Client($this->httpClient);
    }
}

<?php

namespace Martial\OpenCloudSeedbox\Tests\Upload\Freebox;

use GuzzleHttp\ClientInterface;
use Martial\OpenCloudSeedbox\Upload\Freebox\FreeboxAuthenticationProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class FreeboxAuthenticationProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $httpClient;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $response;

    /**
     * @var FreeboxAuthenticationProvider
     */
    public $provider;

    /**
     * @var string
     */
    public $host;

    /**
     * @var int
     */
    public $port;

    public function testGetApplicationToken()
    {
        $this->getApplicationToken();
    }

    /**
     * @expectedException \Martial\OpenCloudSeedbox\Upload\Freebox\FreeboxAuthenticationException
     */
    public function testGetApplicationTokenShouldThrowAnExceptionOnError()
    {
        $this->getApplicationToken(false);
    }

    public function testGetAuthorizationStatusShouldReturnGrantedWhenTheAuthorizationIsGranted()
    {
        $this->getAuthorizationStatus();
    }

    /**
     * @expectedException \Martial\OpenCloudSeedbox\Upload\Freebox\FreeboxAuthenticationException
     */
    public function testGetAuthorizationStatusShouldThrowAnExceptionOnError()
    {
        $this->getAuthorizationStatus(false);
    }

    public function testGetConnectionStatusShouldReturnAString()
    {
        $this->getConnectionStatus();
    }

    public function testGetConnectionStatusWithExistingSessionToken()
    {
        $this->getConnectionStatus(true, true);
    }

    /**
     * @expectedException \Martial\OpenCloudSeedbox\Upload\Freebox\FreeboxAuthenticationException
     */
    public function testGetConnectionStatusShouldTrowAnExceptionOnError()
    {
        $this->getConnectionStatus(false);
    }

    public function testOpenSessionShouldReturnASessionTokenOnSuccess()
    {
        $this->openSession();
    }

    /**
     * @expectedException \Martial\OpenCloudSeedbox\Upload\Freebox\FreeboxAuthenticationException
     */
    public function testOpenSessionShouldThrowAnExceptionOnError()
    {
        $this->openSession(false);
    }

    protected function getApplicationToken($success = true)
    {
        $params = [
            'app_id' => 'io.john-do.seedbox',
            'app_name' => 'Open Cloud Seedbox',
            'app_version' => '1.0.0',
            'device_name' => 'Seedbox'
        ];

        if ($success) {
            $jsonResponse = <<<JSON
{
   "success": true,
   "result": {
      "app_token": "dyNYgfK0Ya6FWGqq83sBHa7TwzWo+pg4fDFUJHShcjVYzTfaRrZzm93p7OTAfH/0",
      "track_id": 42
   }
}
JSON;
        } else {
            $jsonResponse = $this->getJsonResponseError();
        }

        $this
            ->httpClient
            ->expects($this->once())
            ->method('request')
            ->with('POST', $this->buildUrl('/api/v3/login/authorize'), [
                'json' => $params
            ])
            ->willReturn($this->response);

        $response = $this->getJsonDecodedResponse($jsonResponse);
        $result = $this->provider->getApplicationToken($params);

        if ($success) {
            $this->assertSame($response, $result);
            $this->assertTrue($result['success']);
            $this->assertInternalType('string', $result['result']['app_token']);
        }
    }

    protected function getAuthorizationStatus($success = true)
    {
        $trackId = 42;

        if ($success) {
            $jsonResponse = <<<JSON
{
    "success": true,
    "result": {
        "status": "granted",
        "challenge": "Bj6xMqoe+DCHD44KqBljJ579seOXNWr2"
    }
}
JSON;
        } else {
            $jsonResponse = $this->getJsonResponseError();
        }

        $this
            ->httpClient
            ->expects($this->once())
            ->method('request')
            ->with('GET', $this->buildUrl('/api/v3/login/authorize/' . $trackId))
            ->willReturn($this->response);

        $response = $this->getJsonDecodedResponse($jsonResponse);
        $result = $this->provider->getAuthorizationStatus($trackId);

        if ($success) {
            $this->assertSame($response, $result);
            $this->assertSame('granted', $result['result']['status']);
        }
    }

    protected function getConnectionStatus($success = true, $withSessionToken = false)
    {
        $challenge = 'VzhbtpR4r8CLaJle2QgJBEkyd8JPb0zL';
        $sessionToken = $withSessionToken ? uniqid() : '';

        $options = $withSessionToken ? [
            'headers' => [
                'X-Fbx-App-Auth' => $sessionToken
            ]
        ] : [];

        if ($success) {
            $jsonResponse = <<<JSON
{
    "success": true,
    "result": {
        "logged_in": false,
        "challenge": "$challenge"
    }
}
JSON;
        } else {
            $jsonResponse = $this->getJsonResponseError();
        }

        $this
            ->httpClient
            ->expects($this->once())
            ->method('request')
            ->with('GET', $this->buildUrl('/api/v3/login'), $options)
            ->willReturn($this->response);

        $response = $this->getJsonDecodedResponse($jsonResponse);
        $result = $this->provider->getConnectionStatus($sessionToken);

        if ($success) {
            $this->assertSame($response, $result);
            $this->assertSame($challenge, $result['result']['challenge']);
        }
    }

    protected function openSession($success = true)
    {
        $params = [
            'app_id' => 'io.vendor.app',
            'app_token' => uniqid(),
            'challenge' => uniqid()
        ];

        $sessionToken = '35JYdQSvkcBYK84IFMU7H86clfhS75OzwlQrKlQN1gBch\/Dd62RGzDpgC7YB9jB2';

        if ($success) {
            $jsonResponse = <<<JSON
{
   "success": true,
   "result" : {
         "session_token" : "{$sessionToken}",
         "challenge": "jdGL6CtuJ3Dm7p9nkcIQ8pjB+eLwr4Ya",
         "permissions": {
               "downloader": true
         }
    }
}
JSON;
        } else {
            $jsonResponse = $this->getJsonResponseError();
        }

        $this
            ->httpClient
            ->expects($this->once())
            ->method('request')
            ->with('POST', $this->buildUrl('/api/v3/login/session'), [
                    'json' => [
                        'app_id' => $params['app_id'],
                        'password' => hash_hmac(
                            'sha1',
                            $params['challenge'],
                            $params['app_token']
                        )
                    ]
            ])
            ->willReturn($this->response);

        $response = $this->getJsonDecodedResponse($jsonResponse);
        $result = $this->provider->openSession($params);

        if ($success) {
            $this->assertSame($response, $result);
            $this->assertSame($sessionToken, $result['result']['session_token']);
        }
    }

    protected function setUp()
    {
        $this->httpClient = $this->getMock(ClientInterface::class);
        $this->response = $this->getMock(ResponseInterface::class);
        $this->host = '66.66.66.66';
        $this->port = 8888;
        $this->provider = new FreeboxAuthenticationProvider($this->httpClient);
        $this->provider->setHost($this->host);
        $this->provider->setPort($this->port);
    }

    /**
     * @param string $json
     * @return array
     */
    private function getJsonDecodedResponse($json)
    {
        $stream = $this
            ->getMockBuilder(StreamInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this
            ->response
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($stream);

        $json = str_replace('\\', '\\\\', $json);

        $stream
            ->expects($this->once())
            ->method('getContents')
            ->willReturn($json);

        return \GuzzleHttp\json_decode($json, true);
    }

    /**
     * @param string $uri
     * @return string
     */
    private function buildUrl($uri)
    {
        return sprintf('http://%s:%d%s', $this->host, $this->port, $uri);
    }

    /**
     * @return string
     */
    private function getJsonResponseError()
    {
        return <<<JSON
{
    "success": false,
    "msg": "Oops!"
}
JSON;
    }
}

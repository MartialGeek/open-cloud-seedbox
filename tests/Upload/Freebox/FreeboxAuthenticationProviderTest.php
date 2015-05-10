<?php

namespace Martial\Warez\Tests\Upload\Freebox;

use Martial\Warez\Upload\Freebox\FreeboxAuthenticationProvider;

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
     * @expectedException \Martial\Warez\Upload\Freebox\FreeboxAuthenticationException
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
     * @expectedException \Martial\Warez\Upload\Freebox\FreeboxAuthenticationException
     */
    public function testGetAuthorizationStatusShouldThrowAnExceptionOnError()
    {
        $this->getAuthorizationStatus(false);
    }

    public function testGetChallengeValueShouldReturnAString()
    {
        $this->getChallengeValue();
    }

    /**
     * @expectedException \Martial\Warez\Upload\Freebox\FreeboxAuthenticationException
     */
    public function testGetChallengeValueShouldTrowAnExceptionOnError()
    {
        $this->getChallengeValue(false);
    }

    public function testOpenSessionShouldReturnASessionTokenOnSuccess()
    {
        $this->openSession();
    }

    /**
     * @expectedException \Martial\Warez\Upload\Freebox\FreeboxAuthenticationException
     */
    public function testOpenSessionShouldThrowAnExceptionOnError()
    {
        $this->openSession(false);
    }

    protected function getApplicationToken($success = true)
    {
        $params = [
            'app_id' => 'io.john-do.warez',
            'app_name' => 'Warez Companion',
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
            ->method('post')
            ->with($this->equalTo($this->buildUrl('/api/v3/login/authorize')), $this->equalTo([
                'body' => json_encode($params, JSON_FORCE_OBJECT)
            ]))
            ->willReturn($this->response);

        $this->getJsonDecodedResponse($jsonResponse);
        $result = $this->provider->getApplicationToken($params);

        if ($success) {
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
            ->method('get')
            ->with($this->equalTo($this->buildUrl('/api/v3/login/authorize/' . $trackId)))
            ->willReturn($this->response);

        $this->getJsonDecodedResponse($jsonResponse);
        $result = $this->provider->getAuthorizationStatus($trackId);

        if ($success) {
            $this->assertSame('granted', $result['result']['status']);
        }
    }

    protected function getChallengeValue($success = true)
    {
        $challenge = 'VzhbtpR4r8CLaJle2QgJBEkyd8JPb0zL';

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
            ->method('get')
            ->with($this->equalTo($this->buildUrl('/api/v3/login')))
            ->willReturn($this->response);

        $this->getJsonDecodedResponse($jsonResponse);
        $result = $this->provider->getConnectionStatus();

        if ($success) {
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
            ->method('post')
            ->with($this->equalTo($this->buildUrl('/api/v3/login/session')), $this->equalTo([
                    'body' => json_encode([
                        'app_id' => $params['app_id'],
                        'password' => hash_hmac(
                            'sha1',
                            $params['challenge'],
                            $params['app_token']
                        )
                    ], JSON_FORCE_OBJECT)
            ]))
            ->willReturn($this->response);

        $this->getJsonDecodedResponse($jsonResponse);
        $result = $this->provider->openSession($params);

        if ($success) {
            $this->assertSame($sessionToken, $result['result']['session_token']);
        }
    }

    protected function setUp()
    {
        $this->httpClient = $this->getMock('\GuzzleHttp\ClientInterface');
        $this->response = $this->getMock('\GuzzleHttp\Message\ResponseInterface');
        $this->host = '66.66.66.66';
        $this->port = 8888;
        $this->provider = new FreeboxAuthenticationProvider($this->httpClient);
        $this->provider->setHost($this->host);
        $this->provider->setPort($this->port);
    }

    /**
     * @param string $json
     */
    private function getJsonDecodedResponse($json)
    {
        $json = str_replace('\\', '\\\\', $json);

        $this
            ->response
            ->expects($this->once())
            ->method('json')
            ->willReturn(json_decode($json, true));
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

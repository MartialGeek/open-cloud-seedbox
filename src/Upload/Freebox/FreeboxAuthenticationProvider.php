<?php

namespace Martial\Warez\Upload\Freebox;

use GuzzleHttp\ClientInterface;

class FreeboxAuthenticationProvider implements FreeboxAuthenticationProviderInterface
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @param ClientInterface $httpClient
     */
    public function __construct(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Registers the host of the Freebox.
     *
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * Registers the port of the Freebox.
     *
     * @param int $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * Generates an application token with the given parameters.
     * The parameters must provide these keys:
     * <ul>
     * <li>app_id</li>
     * <li>app_name</li>
     * <li>app_version</li>
     * <li>device_name</li>
     * </ul>
     *
     * The result will look like this:
     * <code>
     * [
     *   "success" => true,
     *   "result" => [
     *     "app_token" => "dyNYgfK0Ya6FWGqq83sBHa7TwzWo+pg4fDFUJHShcjVYzTfaRrZzm93p7OTAfH/0",
     *     "track_id" => 42
     *   ]
     * ]
     * </code>
     *
     * @param array $params
     * @return array
     * @throws FreeboxAuthenticationException
     */
    public function getApplicationToken(array $params)
    {
        $response = $this
            ->httpClient
            ->post($this->buildUrl('/api/v3/login/authorize'), [
                'body' => json_encode($params, JSON_FORCE_OBJECT)
            ])
            ->json();

        $this->checkResponseStatus($response);

        return $response;
    }

    /**
     * Returns the status of the authorization for the given track ID.
     * Here is an example of result:
     * <code>
     * [
     *   "success" => true,
     *   "result" => [
     *     "status" => "granted",
     *     "challenge" => "Bj6xMqoe+DCHD44KqBljJ579seOXNWr2"
     *   ]
     * ]
     * </code>
     *
     * @param int $trackId
     * @return array
     * @throws FreeboxAuthenticationException
     */
    public function getAuthorizationStatus($trackId)
    {
        $response = $this
            ->httpClient
            ->get($this->buildUrl('/api/v3/login/authorize/' . $trackId))
            ->json();

        $this->checkResponseStatus($response);

        return $response;
    }

    /**
     * Checks if the application is logged in. Useful for regenerating a new challenge.
     * Here is an example of result:
     * <code>
     * [
     *   "success" => true,
     *   "result" => [
     *     "logged_in" => false,
     *     "challenge" => "Bj6xMqoe+DCHD44KqBljJ579seOXNWr2"
     *   ]
     * ]
     * </code>
     *
     * @param string $sessionToken
     * @return array
     * @throws FreeboxAuthenticationException
     */
    public function getConnectionStatus($sessionToken = '')
    {
        $headers = !empty($sessionToken) ? [
            'headers' => [
                'X-Fbx-App-Auth' => $sessionToken
            ]
        ] : [];

        $response = $this
            ->httpClient
            ->get($this->buildUrl('/api/v3/login'), $headers)
            ->json();

        $this->checkResponseStatus($response);

        return $response;
    }

    /**
     * Open a new session with the given parameters.
     * The parameters must provide these keys:
     * <ul>
     * <li>app_id</li>
     * <li>app_token</li>
     * <li>challenge</li>
     * </ul>
     *
     * Here is an example of valid response:
     * <code>
     * [
     *   "success" => true,
     *   "result" => [
     *     "session_token" => "dyNYgfK0Ya6FWGqq83sBHa7TwzWo+pg4fDFUJHShcjVYzTfaRrZzm93p7OTAfH/0",
     *     "challenge" => "Bj6xMqoe+DCHD44KqBljJ579seOXNWr2",
     *     "permissions" => [
     *       "downloader" => true
     *     ]
     *   ]
     * ]
     * </code>
     *
     * @param array $params
     * @return array
     * @throws FreeboxAuthenticationException
     */
    public function openSession(array $params)
    {
        $body = json_encode([
            'app_id' => $params['app_id'],
            'password' => hash_hmac(
                'sha1',
                $params['challenge'],
                $params['app_token']
            )
        ]);

        $response = $this
            ->httpClient
            ->post($this->buildUrl('/api/v3/login/session'), [
                'body' => $body
            ])
            ->json();

        $this->checkResponseStatus($response);

        return $response;
    }

    /**
     * Throws an exception on error.
     *
     * @param array $response
     * @throws FreeboxAuthenticationException
     */
    private function checkResponseStatus(array $response)
    {
        if (!$response['success']) {
            throw new FreeboxAuthenticationException($response['msg']);
        }
    }

    /**
     * Returns the full URL of the resource.
     *
     * @param string $uri
     * @return string
     */
    private function buildUrl($uri)
    {
        return sprintf('http://%s:%d%s', $this->host, $this->port, $uri);
    }
}

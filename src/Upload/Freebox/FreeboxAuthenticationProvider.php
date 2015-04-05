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
     * @param ClientInterface $httpClient
     */
    public function __construct(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
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
            ->post('/api/v3/login/authorize', [
                'body' => [
                    'app_id' => $params['app_id'],
                    'app_name' => $params['app_name'],
                    'app_version' => $params['app_version'],
                    'device_name' => $params['device_name'],
                ]
            ])
            ->json();

        $this->checkResponseStatus($response, 'An error occurred on retrieving the Freebox token.');

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
            ->get('/api/v3/login/authorize/' . $trackId)
            ->json();

        $this->checkResponseStatus($response, 'An error occurred on retrieving the authorization status.');

        return $response;
    }

    /**
     * Returns a new challenge value.
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
     * @return array
     * @throws FreeboxAuthenticationException
     */
    public function getChallengeValue()
    {
        $response = $this
            ->httpClient
            ->get('/api/v3/login')
            ->json();

        $this->checkResponseStatus($response, 'An error occurred on retrieving the challenge value.');

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
        $response = $this
            ->httpClient
            ->post('/api/v3/login/session', [
                'body' => [
                    'app_id' => $params['app_id'],
                    'password' => hash_hmac(
                        'sha1',
                        $params['app_token'],
                        $params['challenge']
                    )
                ]
            ])
            ->json();

        $this->checkResponseStatus($response, 'An error occurred on session opening.');

        return $response;
    }

    /**
     * Throws an exception on error.
     *
     * @param array $response
     * @param string $errorMessage
     * @throws FreeboxAuthenticationException
     */
    private function checkResponseStatus(array $response, $errorMessage)
    {
        if (!$response['success']) {
            throw new FreeboxAuthenticationException($errorMessage);
        }
    }
}

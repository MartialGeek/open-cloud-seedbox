<?php

namespace Martial\Warez\Upload\Freebox;

/**
 * This interface is designed to manage the login and authorization features of the Freebox V6 API.
 *
 * First, obtain an app_token with your application parameters. Then track the authorization status (the user must
 * accept your app via the Freebox front panel). Once the status is granted, you can ask for a session_token.
 *
 * <code>
 * $provider = new [YourImplementationOfThisInterface];
 *
 * $appTokenData = $provider->getApplicationToken([
 *     'app_id'        => 'io.vendor-name.app-name',
 *     'app_name'      => 'My Awesome App',
 *     'app_version'   => '1.0.0',
 *     'device_name'   => 'Seedbox of John'
 * ]);
 *
 * $authStatus = $provider->getAuthorizationStatus($appTokenData['result']['status']);
 *
 * if (FreeboxAuthenticationProviderInterface::AUTHORIZATION_STATUS_GRANTED == $authStatus) {
 *     $sessionData = $provider->openSession([
 *         'app_id' => 'io.vendor-name.app-name',
 *         'app_token' => $appTokenData['result']['app_token'],
 *         'challenge' => $authStatus['result']['challenge']
 *     ]);
 *
 *     if (true === $sessionData['success']) {
 *         $sessionToken = $sessionData['result']['session_token'];
 *     }
 * }
 *
 * @see http://dev.freebox.fr/sdk/os/login/
 * @package Martial\Warez\Upload\Freebox
 */
interface FreeboxAuthenticationProviderInterface
{
    const AUTHORIZATION_STATUS_UNKNOWN = 'unknown';
    const AUTHORIZATION_STATUS_PENDING = 'pending';
    const AUTHORIZATION_STATUS_TIMEOUT = 'timeout';
    const AUTHORIZATION_STATUS_GRANTED = 'granted';
    const AUTHORIZATION_STATUS_DENIED  = 'denied';

    /**
     * Registers the host of the Freebox.
     *
     * @param string $host
     */
    public function setHost($host);

    /**
     * Registers the port of the Freebox.
     *
     * @param int $port
     */
    public function setPort($port);

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
    public function getApplicationToken(array $params);

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
    public function getAuthorizationStatus($trackId);

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
     * @return array
     * @param string $sessionToken
     * @throws FreeboxAuthenticationException
     */
    public function getConnectionStatus($sessionToken = '');

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
    public function openSession(array $params);
}

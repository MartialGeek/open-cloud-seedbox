<?php

namespace Martial\Warez\Upload\Freebox;

use GuzzleHttp\ClientInterface;
use Martial\Warez\Upload\UploadException;
use Martial\Warez\Upload\UploadInterface;
use Symfony\Component\HttpFoundation\File\File;

class FreeboxUploaderAdapter implements UploadInterface
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $sessionToken;

    /**
     * @param ClientInterface $client
     * @param array $config
     */
    public function __construct(ClientInterface $client, array $config)
    {
        $this->httpClient = $client;
        $this->config = $config;
    }
    /**
     * Uploads the given file on the target.
     *
     * @param File $file
     * @throws UploadException
     */
    public function upload(File $file)
    {
        if (is_null($this->sessionToken)) {
            $this->authenticate();
        }
    }

    /**
     * Authenticates the application through the Freebox API.
     */
    protected function authenticate()
    {
        $trackIdData = $this
            ->httpClient
            ->post('/api/v3/login/authorize', [
                'body' => [
                    'app_id' => $this->config['app_id'],
                    'app_name' => $this->config['app_name'],
                    'app_version' => $this->config['app_version'],
                    'device_name' => $this->config['device_name'],
                ]
            ])
            ->json();

        $authorizeTrackIdData = $this
            ->httpClient
            ->get('/api/v3/login/authorize/' . $trackIdData['result']['track_id'])
            ->json();

        if ($authorizeTrackIdData['result']['status'] != 'granted') {
            // TODO: Implement the behavior when the authorization is pending.
        }

        $challengeData = $this
            ->httpClient
            ->get('/api/v3/login')
            ->json();

        $openSessionData = $this
            ->httpClient
            ->post('/api/v3/login/session', [
                'body' => [
                    'app_id' => $this->config['app_id'],
                    'password' => hash_hmac(
                        'sha1',
                        $trackIdData['result']['app_token'],
                        $challengeData['result']['challenge']
                    )
                ]
            ])
            ->json();

        $this->sessionToken = $openSessionData['result']['session_token'];
    }
}

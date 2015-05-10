<?php

namespace Martial\Warez\Upload\Freebox;

use GuzzleHttp\ClientInterface;
use Martial\Warez\Settings\Entity\FreeboxSettingsEntity;
use Martial\Warez\Settings\FreeboxSettings;
use Martial\Warez\Settings\FreeboxSettingsDataTransformer;
use Martial\Warez\Upload\UploadInterface;
use Martial\Warez\User\Entity\User;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FreeboxManager
{
    const HTTP_HEADER_USER_EMAIL = 'X-App-User-Email';
    const HTTP_HEADER_USER_PASSWORD = 'X-App-User-Password';

    /**
     * @var UploadInterface
     */
    private $upload;

    /**
     * @var FreeboxAuthenticationProviderInterface
     */
    private $authentication;

    /**
     * @var FreeboxSettings
     */
    private $settingsManager;

    /**
     * @var FreeboxSettingsDataTransformer
     */
    private $dataTransformer;

    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @param UploadInterface $upload
     * @param FreeboxAuthenticationProviderInterface $authenticationProvider
     * @param FreeboxSettings $settings
     * @param FreeboxSettingsDataTransformer $dataTransformer
     * @param ClientInterface $httpClient
     * @param UrlGeneratorInterface $urlGeneratorInterface
     */
    public function __construct(
        UploadInterface $upload,
        FreeboxAuthenticationProviderInterface $authenticationProvider,
        FreeboxSettings $settings,
        FreeboxSettingsDataTransformer $dataTransformer,
        ClientInterface $httpClient,
        UrlGeneratorInterface $urlGeneratorInterface
    ) {
        $this->upload = $upload;
        $this->authentication = $authenticationProvider;
        $this->settingsManager = $settings;
        $this->dataTransformer = $dataTransformer;
        $this->httpClient = $httpClient;
        $this->urlGenerator = $urlGeneratorInterface;
    }

    /**
     * Sends a permission request to the Freebox. The user must allow the application on the front panel of the box.
     *
     * @param User $user
     * @return array
     * @throws FreeboxAuthenticationException
     */
    public function askUserPermission(User $user)
    {
        $settings = $this->settingsManager->getSettings($user);
        $this->configureAuthenticationProvider($settings);
        $appToken = $this->authentication->getApplicationToken([
            'app_id' => $settings->getAppId(),
            'app_name' => $settings->getAppName(),
            'app_version' => $settings->getAppVersion(),
            'device_name' => $settings->getDeviceName()
        ]);

        $settings->setAppToken($appToken['result']['app_token']);
        $this->settingsManager->updateSettings($settings, $user);

        return $appToken;
    }

    /**
     * @param User $user
     * @param int $trackId
     * @return array
     * @throws FreeboxAuthorizationDeniedException
     * @throws FreeboxAuthorizationException
     * @throws FreeboxAuthorizationPendingException
     * @throws FreeboxAuthorizationTimeoutException
     */
    public function trackAuthorizationStatus(User $user, $trackId)
    {
        $settings = $this->settingsManager->getSettings($user);
        $this->configureAuthenticationProvider($settings);
        $authStatus = $this->authentication->getAuthorizationStatus($trackId);

        switch ($authStatus['result']['status']) {
            case FreeboxAuthenticationProviderInterface::AUTHORIZATION_STATUS_GRANTED:
                $settings->setChallenge($authStatus['result']['challenge']);
                $this->settingsManager->updateSettings($settings, $user);
                break;
            case FreeboxAuthenticationProviderInterface::AUTHORIZATION_STATUS_DENIED:
                throw new FreeboxAuthorizationDeniedException();
            case FreeboxAuthenticationProviderInterface::AUTHORIZATION_STATUS_PENDING:
                throw new FreeboxAuthorizationPendingException();
            case FreeboxAuthenticationProviderInterface::AUTHORIZATION_STATUS_TIMEOUT:
                throw new FreeboxAuthorizationTimeoutException();
            case FreeboxAuthenticationProviderInterface::AUTHORIZATION_STATUS_UNKNOWN:
            default:
                throw new FreeboxAuthorizationException();
        }

        return $authStatus;
    }

    /**
     * Return true if the application is already logged in.
     *
     * @param User $user
     * @return bool
     */
    public function isLoggedIn(User $user)
    {
        $settings = $this->settingsManager->getSettings($user);
        $this->configureAuthenticationProvider($settings);
        $sessionToken = $settings->getSessionToken();

        if (is_null($sessionToken)) {
            $sessionToken = '';
        }

        $status = $this->authentication->getConnectionStatus($sessionToken);
        $settings->setChallenge($status['result']['challenge']);
        $this->settingsManager->updateSettings($settings, $user);

        return $status['result']['logged_in'];
    }

    /**
     * Opens a Freebox session and returns a session token.
     *
     * @param User $user
     * @throws FreeboxSessionException
     */
    public function openSession(User $user)
    {
        $settings = $this->settingsManager->getSettings($user);

        if (!$settings->getChallenge() || !$settings->getAppToken()) {
            throw new FreeboxSessionException('You need a challenge and an application token before opening a session');
        }

        $this->configureAuthenticationProvider($settings);

        try {
            $session = $this->authentication->openSession([
                'app_id' => $settings->getAppId(),
                'app_token' => $settings->getAppToken(),
                'challenge' => $settings->getChallenge()
            ]);
        } catch (FreeboxAuthenticationException $e) {
            throw new FreeboxSessionException($e->getMessage());
        }

        $settings->setSessionToken($session['result']['session_token']);
        $this->settingsManager->updateSettings($settings, $user);
    }

    /**
     * Uploads the given file.
     *
     * @param File $file
     * @param User $user
     * @throws FreeboxSessionException
     */
    public function uploadFile(File $file, User $user)
    {
        $settings = $this->settingsManager->getSettings($user);
        $token = $settings->getSessionToken();
        $freeboxUrl = sprintf('http://%s:%d', $settings->getTransportHost(), $settings->getTransportPort());

        if (is_null($token)) {
            throw new FreeboxSessionException();
        }

        $this->upload->upload($file, $freeboxUrl, ['session_token' => $token]);
    }

    /**
     * Returns an array of the Freebox settings value for the given user.
     *
     * @param User $user
     * @param string $email
     * @param string $rawPassword
     * @param string $baseUrl
     */
    public function exportSettings(User $user, $email, $rawPassword, $baseUrl)
    {
        $settings = $this->settingsManager->getSettings($user);
        $toArray = $this->dataTransformer->toArray($settings);
        $url = $baseUrl . $this->urlGenerator->generate('freebox_import_settings');

        $this
            ->httpClient
            ->post($url, [
                'body' => [
                    'settings' => $toArray
                ],
                'headers' => [
                    self::HTTP_HEADER_USER_EMAIL => $email,
                    self::HTTP_HEADER_USER_PASSWORD => $rawPassword
                ]
            ]);
    }

    /**
     * Imports the settings in the given user profile.
     *
     * @param User $user
     * @param array $arraySettings
     */
    public function importSettings(User $user, array $arraySettings)
    {
        $settings = $this->dataTransformer->toObject($arraySettings);
        $this->settingsManager->updateSettings($settings, $user);
    }

    private function configureAuthenticationProvider(FreeboxSettingsEntity $settings)
    {
        $this->authentication->setHost($settings->getTransportHost());
        $this->authentication->setPort($settings->getTransportPort());
    }
}

<?php

namespace Martial\Warez\Upload\Freebox;

use Martial\Warez\Settings\Entity\FreeboxSettingsEntity;
use Martial\Warez\Settings\FreeboxSettings;
use Martial\Warez\Upload\UploadInterface;
use Martial\Warez\User\Entity\User;
use Symfony\Component\HttpFoundation\File\File;

class FreeboxManager
{
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
     * @param UploadInterface $upload
     * @param FreeboxAuthenticationProviderInterface $authenticationProvider
     * @param FreeboxSettings $settings
     */
    public function __construct(
        UploadInterface $upload,
        FreeboxAuthenticationProviderInterface $authenticationProvider,
        FreeboxSettings $settings
    ) {
        $this->upload = $upload;
        $this->authentication = $authenticationProvider;
        $this->settingsManager = $settings;
    }

    /**
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

        if (!$appToken['success']) {
            throw new FreeboxAuthenticationException();
        }

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
     * Opens a Freebox session and returns a session token.
     *
     * @param User $user
     * @param string $appToken
     * @param string $challenge
     * @throws FreeboxSessionException
     */
    public function openSession(User $user, $appToken, $challenge)
    {
        $settings = $this->settingsManager->getSettings($user);
        $this->configureAuthenticationProvider($settings);

        $session = $this->authentication->openSession([
            'app_id' => $settings->getAppId(),
            'app_token' => $appToken,
            'challenge' => $challenge
        ]);

        if (false === $session['success']) {
            throw new FreeboxSessionException($session['msg']);
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
        $token = $this->settingsManager->getSettings($user)->getSessionToken();

        if (is_null($token)) {
            throw new FreeboxSessionException();
        }

        $this->upload->upload($file, ['session_token' => $token]);
    }

    private function configureAuthenticationProvider(FreeboxSettingsEntity $settings)
    {
        $this->authentication->setHost($settings->getTransportHost());
        $this->authentication->setPort($settings->getTransportPort());
    }
}

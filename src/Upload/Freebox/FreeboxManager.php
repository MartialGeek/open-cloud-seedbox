<?php

namespace Martial\Warez\Upload\Freebox;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use Martial\Warez\Filesystem\ZipArchiver;
use Martial\Warez\MessageQueuing\Freebox\FreeboxMessageProducer;
use Martial\Warez\Settings\Entity\FreeboxSettingsEntity;
use Martial\Warez\Settings\FreeboxSettings;
use Martial\Warez\Settings\FreeboxSettingsDataTransformer;
use Martial\Warez\Upload\UploadInterface;
use Martial\Warez\User\Entity\User;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FreeboxManager
{
    const HTTP_HEADER_USER_EMAIL = 'X-App-User-Email';
    const HTTP_HEADER_USER_PASSWORD = 'X-App-User-Password';
    const UPLOAD_TYPE_REGULAR = 'regular';
    const UPLOAD_TYPE_ARCHIVE = 'archive';

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
     * @var ZipArchiver
     */
    private $archiver;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var string
     */
    private $downloadDir;

    /**
     * @var string
     */
    private $archivePath;

    /**
     * @var FreeboxMessageProducer
     */
    private $messageProducer;

    /**
     * @param UploadInterface $upload
     * @param FreeboxAuthenticationProviderInterface $authenticationProvider
     * @param FreeboxSettings $settings
     * @param FreeboxSettingsDataTransformer $dataTransformer
     * @param ClientInterface $httpClient
     * @param UrlGeneratorInterface $urlGeneratorInterface
     * @param ZipArchiver $archiver
     * @param Filesystem $fs
     * @param FreeboxMessageProducer $messageProducer
     */
    public function __construct(
        UploadInterface $upload,
        FreeboxAuthenticationProviderInterface $authenticationProvider,
        FreeboxSettings $settings,
        FreeboxSettingsDataTransformer $dataTransformer,
        ClientInterface $httpClient,
        UrlGeneratorInterface $urlGeneratorInterface,
        ZipArchiver $archiver,
        Filesystem $fs,
        FreeboxMessageProducer $messageProducer
    ) {
        $this->upload = $upload;
        $this->authentication = $authenticationProvider;
        $this->settingsManager = $settings;
        $this->dataTransformer = $dataTransformer;
        $this->httpClient = $httpClient;
        $this->urlGenerator = $urlGeneratorInterface;
        $this->archiver = $archiver;
        $this->fs = $fs;
        $this->messageProducer = $messageProducer;
    }

    /**
     * @param string $downloadDir
     */
    public function setDownloadDir($downloadDir)
    {
        $this->downloadDir = $downloadDir;
    }

    /**
     * Sets the path where the archive are generated.
     *
     * @param string $archivePath
     */
    public function setArchivePath($archivePath)
    {
        $this->archivePath = $archivePath;
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

        if (!$settings->getAppToken()) {
            throw new FreeboxSessionException('You need and an application token before opening a session');
        }

        // Renew the challenge value.
        $this->isLoggedIn($user);

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
     * @param string $fileName
     * @param User $user
     * @throws FreeboxSessionException
     */
    public function uploadFile($fileName, User $user)
    {
        $filePath = $this->downloadDir . '/' . $fileName;

        if (is_dir($filePath)) {
            $this->messageProducer->generateArchiveAndUpload($fileName, $user->getId());
        } else {
            $this->upload($filePath, $user);
        }
    }

    /**
     * @param string $fileName
     * @param User $user
     */
    public function generateArchiveAndUpload($fileName, User $user)
    {
        $filePath = $this->downloadDir . '/' . $fileName;
        $fileInfo = new \SplFileInfo($filePath);
        $archivePath = $this->archivePath . '/' . $fileInfo->getBasename('.' . $fileInfo->getExtension()) . '.zip';
        $this->archiver->createArchive($fileInfo, $archivePath);
        $this->upload($archivePath, $user, self::UPLOAD_TYPE_ARCHIVE);
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

    /**
     * @param FreeboxSettingsEntity $settings
     */
    private function configureAuthenticationProvider(FreeboxSettingsEntity $settings)
    {
        $this->authentication->setHost($settings->getTransportHost());
        $this->authentication->setPort($settings->getTransportPort());
    }

    /**
     * @param string $filePath
     * @param User $user
     * @param string $uploadType
     * @throws FreeboxSessionException
     */
    private function upload($filePath, User $user, $uploadType = self::UPLOAD_TYPE_REGULAR)
    {
        $file = new File($filePath);
        $settings = $this->settingsManager->getSettings($user);
        $freeboxUrl = sprintf('http://%s:%d', $settings->getTransportHost(), $settings->getTransportPort());

        if (is_null($settings->getSessionToken())) {
            $settings = $this->openNewSession($user);
        }

        $uploadOptions = [
            'session_token' => $settings->getSessionToken(),
            'upload_type' => $uploadType
        ];

        try {
            $this->upload->upload($file, $freeboxUrl, $uploadOptions);
        } catch (ClientException $e) {
            if ($e->getCode() == 403 || $e->getCode() == 401) {
                $settings = $this->openNewSession($user);
                $uploadOptions['session_token'] = $settings->getSessionToken();
                $this->upload->upload($file, $freeboxUrl, $uploadOptions);
            } else {
                throw new FreeboxSessionException(
                    'An error prevents to add the element to the downloads queue',
                    0,
                    $e
                );
            }
        }
    }

    /**
     * @param User $user
     * @return FreeboxSettingsEntity
     * @throws FreeboxSessionException
     */
    private function openNewSession(User $user)
    {
        $this->openSession($user);

        return $this->settingsManager->getSettings($user);
    }
}

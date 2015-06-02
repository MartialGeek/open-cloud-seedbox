<?php

namespace Martial\Warez\Tests\Upload\Freebox;

use Martial\Warez\Upload\Freebox\FreeboxAuthenticationException;
use Martial\Warez\Upload\Freebox\FreeboxManager;
use Martial\Warez\Upload\Freebox\FreeboxSessionException;

class FreeboxManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FreeboxManager
     */
    public $freeboxManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $uploadManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $authenticationProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $settingsManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $dataTransformer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $httpClient;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $urlGenerator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $archiver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $fs;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $messageProducer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $user;

    /**
     * @var string
     */
    public $downloadDir;

    /**
     * @var string
     */
    public $archivePath;

    protected function setUp()
    {
        $this->uploadManager = $this->getMock('\Martial\Warez\Upload\UploadInterface');
        $this->httpClient = $this->getMock('\GuzzleHttp\ClientInterface');
        $this->urlGenerator = $this->getMock('\Symfony\Component\Routing\Generator\UrlGeneratorInterface');

        $this->authenticationProvider = $this
            ->getMock('\Martial\Warez\Upload\Freebox\FreeboxAuthenticationProviderInterface');

        $this->settingsManager = $this
            ->getMockBuilder('\Martial\Warez\Settings\FreeboxSettings')
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataTransformer = $this
            ->getMockBuilder('\Martial\Warez\Settings\FreeboxSettingsDataTransformer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->archiver = $this
            ->getMockBuilder('\Martial\Warez\Filesystem\ZipArchiver')
            ->disableOriginalConstructor()
            ->getMock();

        $this->fs = $this
            ->getMockBuilder('\Symfony\Component\Filesystem\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageProducer = $this
            ->getMockBuilder('\Martial\Warez\MessageQueuing\Freebox\FreeboxMessageProducer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->user = $this
            ->getMockBuilder('\Martial\Warez\User\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();

        $this->downloadDir = '/path/to/download/dir';
        $this->archivePath = '/path/to/archive/dir';

        $this->freeboxManager = new FreeboxManager(
            $this->uploadManager,
            $this->authenticationProvider,
            $this->settingsManager,
            $this->dataTransformer,
            $this->httpClient,
            $this->urlGenerator,
            $this->archiver,
            $this->fs,
            $this->messageProducer
        );

        $this->freeboxManager->setArchivePath($this->archivePath);
        $this->freeboxManager->setDownloadDir($this->downloadDir);
    }

    public function testAskUserPermission()
    {
        $settings = $this->getSettings($this->once());
        $this->configureAuthenticationProvider($settings);

        $appData = [
            'app_id' => $this->getAppId($settings),
            'app_name' => $this->getAppName($settings),
            'app_version' => $this->getAppVersion($settings),
            'device_name' => $this->getDeviceName($settings)
        ];

        $appToken = $this->getAppTokenFromApi();

        $this
            ->authenticationProvider
            ->expects($this->once())
            ->method('getApplicationToken')
            ->with($this->equalTo($appData))
            ->willReturn($appToken);

        $settings
            ->expects($this->once())
            ->method('setAppToken')
            ->with($this->equalTo($appToken['result']['app_token']));

        $this->updateSettings($settings, $this->once());

        $result = $this->freeboxManager->askUserPermission($this->user);
        $this->assertSame($appToken, $result);
    }

    public function testTrackAuthorizationStatusGranted()
    {
        $this->trackAuthorizationStatus('granted');
    }

    /**
     * @expectedException \Martial\Warez\Upload\Freebox\FreeboxAuthorizationDeniedException
     */
    public function testTrackAuthorizationStatusDenied()
    {
        $this->trackAuthorizationStatus('denied');
    }

    /**
     * @expectedException \Martial\Warez\Upload\Freebox\FreeboxAuthorizationPendingException
     */
    public function testTrackAuthorizationStatusPending()
    {
        $this->trackAuthorizationStatus('pending');
    }

    /**
     * @expectedException \Martial\Warez\Upload\Freebox\FreeboxAuthorizationTimeoutException
     */
    public function testTrackAuthorizationStatusTimeout()
    {
        $this->trackAuthorizationStatus('timeout');
    }

    /**
     * @expectedException \Martial\Warez\Upload\Freebox\FreeboxAuthorizationException
     */
    public function testTrackAuthorizationStatusUnknownError()
    {
        $this->trackAuthorizationStatus('unknown');
    }

    public function testIsLoggedIn()
    {
        $this->isLoggedIn(true);
    }

    public function testIsNotLoggedIn()
    {
        $this->isLoggedIn(false);
    }

    public function testOpenSessionWithSuccess()
    {
        $this->openSession();
    }

    public function testOpenSessionWithMissingAppToken()
    {
        try {
            $this->openSession('missing_app_token');
        } catch (FreeboxSessionException $e) {
            $message = 'You need and an application token before opening a session';
            $this->assertSame($message, $e->getMessage());
        }

        if (!isset($e)) {
            $this->fail('FreeboxSessionException was not thrown.');
        }
    }

    /**
     * @expectedException \Martial\Warez\Upload\Freebox\FreeboxSessionException
     */
    public function testOpenSessionWithAuthenticationError()
    {
        $this->openSession('auth_error');
    }

    public function testUploadRegularFile()
    {
        $this->upload();
    }

    public function testUploadArchive()
    {
        $this->upload('archive');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getSettingsEntity()
    {
        return $this
            ->getMockBuilder('\Martial\Warez\Settings\Entity\FreeboxSettingsEntity')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param \PHPUnit_Framework_MockObject_Matcher_Invocation $matcher
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getSettings(\PHPUnit_Framework_MockObject_Matcher_Invocation $matcher)
    {
        $settings = $this->getSettingsEntity();

        $this
            ->settingsManager
            ->expects($matcher)
            ->method('getSettings')
            ->with($this->equalTo($this->user))
            ->willReturn($settings);

        return $settings;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $settingsEntity
     */
    private function configureAuthenticationProvider(\PHPUnit_Framework_MockObject_MockObject $settingsEntity)
    {
        $host = $this->getTransportHost($settingsEntity);
        $port = $this->getTransportPort($settingsEntity);

        $this
            ->authenticationProvider
            ->expects($this->once())
            ->method('setHost')
            ->with($this->equalTo($host));

        $this
            ->authenticationProvider
            ->expects($this->once())
            ->method('setPort')
            ->with($this->equalTo($port));
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $settingsEntity
     * @param \PHPUnit_Framework_MockObject_Matcher_Invocation $matcher
     * @return string
     */
    private function getSessionToken(
        \PHPUnit_Framework_MockObject_MockObject $settingsEntity,
        \PHPUnit_Framework_MockObject_Matcher_Invocation $matcher = null
    ) {
        if (is_null($matcher)) {
            $matcher = $this->once();
        }

        $sessionToken = uniqid();

        $settingsEntity
            ->expects($matcher)
            ->method('getSessionToken')
            ->willReturn($sessionToken);

        return $sessionToken;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $settingsEntity
     * @return string
     */
    private function getTransportHost(\PHPUnit_Framework_MockObject_MockObject $settingsEntity)
    {
        $transportHost = '42.42.42.42';

        $settingsEntity
            ->expects($this->once())
            ->method('getTransportHost')
            ->willReturn($transportHost);

        return $transportHost;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $settingsEntity
     * @return string
     */
    private function getTransportPort(\PHPUnit_Framework_MockObject_MockObject $settingsEntity)
    {
        $transportPort = 8888;

        $settingsEntity
            ->expects($this->once())
            ->method('getTransportPort')
            ->willReturn($transportPort);

        return $transportPort;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $settingsEntity
     * @return string
     */
    private function getAppId(\PHPUnit_Framework_MockObject_MockObject $settingsEntity)
    {
        $appId = uniqid();

        $settingsEntity
            ->expects($this->once())
            ->method('getAppId')
            ->willReturn($appId);

        return $appId;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $settingsEntity
     * @return string
     */
    private function getAppName(\PHPUnit_Framework_MockObject_MockObject $settingsEntity)
    {
        $appName = 'My Torrent Companion';

        $settingsEntity
            ->expects($this->once())
            ->method('getAppName')
            ->willReturn($appName);

        return $appName;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $settingsEntity
     * @return string
     */
    private function getAppVersion(\PHPUnit_Framework_MockObject_MockObject $settingsEntity)
    {
        $appVersion = '1.0';

        $settingsEntity
            ->expects($this->once())
            ->method('getAppVersion')
            ->willReturn($appVersion);

        return $appVersion;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $settingsEntity
     * @return string
     */
    private function getDeviceName(\PHPUnit_Framework_MockObject_MockObject $settingsEntity)
    {
        $deviceName = 'My seedbox';

        $settingsEntity
            ->expects($this->once())
            ->method('getDeviceName')
            ->willReturn($deviceName);

        return $deviceName;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $settingsEntity
     * @return string
     */
    private function getChallenge(\PHPUnit_Framework_MockObject_MockObject $settingsEntity)
    {
        $challenge = uniqid();

        $settingsEntity
            ->expects($this->once())
            ->method('getChallenge')
            ->willReturn($challenge);

        return $challenge;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $settingsEntity
     * @return string
     */
    private function getAppToken(\PHPUnit_Framework_MockObject_MockObject $settingsEntity)
    {
        $appToken = uniqid();

        $settingsEntity
            ->expects($this->once())
            ->method('getAppToken')
            ->willReturn($appToken);

        return $appToken;
    }

    /**
     * @return array
     */
    private function getAppTokenFromApi()
    {
        return [
            "success" => true,
            "result" => [
                "app_token" => "dyNYgfK0Ya6FWGqq83sBHa7TwzWo+pg4fDFUJHShcjVYzTfaRrZzm93p7OTAfH/0",
                "track_id" => 42
            ]
        ];
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $settingsEntity
     * @param \PHPUnit_Framework_MockObject_Matcher_Invocation $matcher
     */
    private function updateSettings(
        \PHPUnit_Framework_MockObject_MockObject $settingsEntity,
        \PHPUnit_Framework_MockObject_Matcher_Invocation $matcher
    ) {
        $this
            ->settingsManager
            ->expects($matcher)
            ->method('updateSettings')
            ->with($this->equalTo($settingsEntity), $this->equalTo($this->user));
    }

    private function trackAuthorizationStatus($status)
    {
        $trackId = 42;
        $settings = $this->getSettings($this->once());
        $this->configureAuthenticationProvider($settings);

        $authData = [
            "success" => true,
            "result" => [
                "status" => $status,
                "challenge" => "Bj6xMqoe+DCHD44KqBljJ579seOXNWr2"
            ]
        ];

        $this
            ->authenticationProvider
            ->expects($this->once())
            ->method('getAuthorizationStatus')
            ->with($this->equalTo($trackId))
            ->willReturn($authData);

        if ($status == 'granted') {
            $settings
                ->expects($this->once())
                ->method('setChallenge')
                ->with($this->equalTo($authData['result']['challenge']));

            $this->updateSettings($settings, $this->once());
        }

        $result = $this->freeboxManager->trackAuthorizationStatus($this->user, $trackId);

        if ($status == 'granted') {
            $this->assertSame($authData, $result);
        }
    }

    /**
     * @param bool $isLoggedIn
     * @param \PHPUnit_Framework_MockObject_MockObject $settings
     */
    private function isLoggedIn($isLoggedIn, \PHPUnit_Framework_MockObject_MockObject $settings = null)
    {
        $internalCall = true;

        if (is_null($settings)) {
            $internalCall = false;
            $settings = $this->getSettings($this->once());
        }

        $this->configureAuthenticationProvider($settings);

        $connectionData = [
            "success" => true,
            "result" => [
                "logged_in" => $isLoggedIn,
                "challenge" => "Bj6xMqoe+DCHD44KqBljJ579seOXNWr2"
            ]
        ];

        $settings
            ->expects($this->once())
            ->method('getSessionToken')
            ->willReturn(null);

        $this
            ->authenticationProvider
            ->expects($this->once())
            ->method('getConnectionStatus')
            ->with($this->equalTo(''))
            ->willReturn($connectionData);

        $settings
            ->expects($this->once())
            ->method('setChallenge')
            ->with($this->equalTo($connectionData['result']['challenge']));

        if (!$internalCall) {
            $this->updateSettings($settings, $this->once());
            $result = $this->freeboxManager->isLoggedIn($this->user);
            $this->assertSame($isLoggedIn, $result);
        }
    }

    private function openSession($behavior = 'success')
    {
        $getSettingsCalls = $behavior == 'missing_app_token' ? $this->once() : $this->exactly(2);
        $settings = $this->getSettings($getSettingsCalls);
        $appToken = $behavior == 'missing_app_token' ? null : uniqid();
        $getAppTokenCalls = $behavior == 'missing_app_token' ? $this->once() : $this->exactly(2);

        $getAppTokenInvocation = $settings
            ->expects($getAppTokenCalls)
            ->method('getAppToken');

        if ($behavior == 'missing_app_token') {
            $getAppTokenInvocation->willReturn(null);
        } else {
            $getAppTokenInvocation
                ->willReturnOnConsecutiveCalls($appToken, $appToken);
        }

        if ($behavior == 'missing_app_token') {
            $this->freeboxManager->openSession($this->user);
            return;
        }

        $this->isLoggedIn(true, $settings);

        $openSessionData = [
            'app_id' => $this->getAppId($settings),
            'app_token' => $appToken,
            'challenge' => $this->getChallenge($settings)
        ];

        $authData = [
            "success" => true,
            "result" => [
                "session_token" => "dyNYgfK0Ya6FWGqq83sBHa7TwzWo+pg4fDFUJHShcjVYzTfaRrZzm93p7OTAfH/0",
                "challenge" => "Bj6xMqoe+DCHD44KqBljJ579seOXNWr2",
                "permissions" => [
                    "downloader" => true
                ]
            ]
        ];

        $authResult = $behavior == 'auth_error' ?
            $this->throwException(new FreeboxAuthenticationException('Message')) :
            $this->returnValue($authData);

        $this
            ->authenticationProvider
            ->expects($this->once())
            ->method('openSession')
            ->with($this->equalTo($openSessionData))
            ->will($authResult);

        $updateSettingsCalls = $behavior == 'success' ? $this->exactly(2) : $this->once();
        $this->updateSettings($settings, $updateSettingsCalls);

        if ($behavior == 'success') {
            $settings
                ->expects($this->once())
                ->method('setSessionToken')
                ->with($this->equalTo($authData['result']['session_token']));
        }

        $this->freeboxManager->openSession($this->user);
    }

    private function upload($uploadType = 'regular')
    {
        $file = $uploadType == 'regular' ? 'ubuntu-14.04-desktop-amd64.iso.torrent' : 'TestDir';

        if ($uploadType == 'regular') {
            $settings = $this->getSettings($this->once());
            $transportHost = $this->getTransportHost($settings);
            $transportPort = $this->getTransportPort($settings);
            $freeboxUrl = sprintf('http://%s:%d', $transportHost, $transportPort);
            $sessionToken = $this->getSessionToken($settings, $this->exactly(2));

            $uploadOptions = [
                'session_token' => $sessionToken,
                'upload_type' => $uploadType
            ];

            $this
                ->uploadManager
                ->expects($this->once())
                ->method('upload')
                ->with(
                    $this->isInstanceOf('\Symfony\Component\HttpFoundation\File\File'),
                    $this->equalTo($freeboxUrl),
                    $this->equalTo($uploadOptions)
                );
        } elseif ($uploadType == 'archive') {
            $userId = 42;

            $this
                ->user
                ->expects($this->once())
                ->method('getId')
                ->willReturn($userId);

            $this
                ->messageProducer
                ->expects($this->once())
                ->method('generateArchiveAndUpload')
                ->with($this->equalTo($file), $this->equalTo($userId));
        }

        $this->freeboxManager->setDownloadDir(__DIR__ . '/../../Resources/T411');
        $this->freeboxManager->uploadFile($file, $this->user);
    }
}

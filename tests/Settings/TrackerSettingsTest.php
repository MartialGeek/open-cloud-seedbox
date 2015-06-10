<?php

namespace Martial\Warez\Tests\Settings;

use Martial\Warez\Settings\TrackerSettings;

class TrackerSettingsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TrackerSettings
     */
    public $trackerSettings;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $encoder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $em;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $user;

    protected function setUp()
    {
        $this->encoder = $this->getMock('\Martial\Warez\Security\EncoderInterface');
        $this->em = $this->getMock('\Doctrine\ORM\EntityManagerInterface');
        $this->trackerSettings = new TrackerSettings($this->encoder, $this->em);

        $this->user = $this
            ->getMockBuilder('\Martial\Warez\User\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetExistingSettings()
    {
        $clearPassword = 'aP@ssW0rd';
        $encodedPassword = uniqid();
        $currentSettings = $this->getSettingsEntity();

        $repo = $this->getMock('\Doctrine\Common\Persistence\ObjectRepository');
        $this->getRepository($repo);
        $this->findOneByUser($repo, $currentSettings);

        $this->getPassword(
            $currentSettings,
            $this->once(),
            new \PHPUnit_Framework_MockObject_Stub_Return($encodedPassword)
        );

        $this->setPassword(
            $currentSettings,
            $this->once(),
            new \PHPUnit_Framework_MockObject_Matcher_Parameters([$clearPassword])
        );

        $this->decodePassword($encodedPassword, $clearPassword);

        $result = $this->trackerSettings->getSettings($this->user);
        $this->assertSame($currentSettings, $result);
    }

    public function testGetEmptySettings()
    {
        $clearPassword = null;
        $encodedPassword = null;
        $currentSettings = null;

        $repo = $this->getMock('\Doctrine\Common\Persistence\ObjectRepository');
        $this->getRepository($repo);
        $this->findOneByUser($repo, $currentSettings);
        $this->decodePassword($encodedPassword, $clearPassword);

        $result = $this->trackerSettings->getSettings($this->user);
        $this->assertInstanceOf('\Martial\Warez\Settings\Entity\TrackerSettingsEntity', $result);
        $this->assertSame($this->user, $result->getUser());
    }

    public function testUpdateExistingSettingsWithTheSamePassword()
    {
        $this->updateSettings();
    }

    public function testUpdateExistingSettingsWithDifferentPassword()
    {
        $this->updateSettings(false);
    }

    public function testUpdateNonExistingSettings()
    {
        $this->updateSettings(true, false);
    }

    private function updateSettings($samePassword = true, $alreadyExists = true)
    {
        $clearCurrentPassword = 'aP@ssW0rd';
        $encodedCurrentPassword = uniqid();
        $currentSettings = $this->getSettingsEntity();
        $clearNewPassword = $samePassword ? $clearCurrentPassword : uniqid();
        $encodedNewPassword = uniqid();
        $newSettings = $this->getSettingsEntity();
        $username = 'Joe';

        $repo = $this->getMock('\Doctrine\Common\Persistence\ObjectRepository');
        $this->getRepository($repo);
        $this->findOneByUser($repo, $currentSettings);

        $getPasswordInvocations = $samePassword ? $this->exactly(2) : $this->exactly(3);
        $getPasswordStubs = [$encodedCurrentPassword, $clearCurrentPassword];

        if (!$samePassword) {
            $getPasswordStubs[] = $clearNewPassword;
            $this->encodePassword($clearNewPassword, $encodedNewPassword);
        }

        $this->getPassword(
            $currentSettings,
            $getPasswordInvocations,
            new \PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls($getPasswordStubs)
        );

        $this->getPassword($newSettings, $this->once(), new \PHPUnit_Framework_MockObject_Stub_Return($clearNewPassword));
        $setPasswordInvocations = $samePassword ? $this->once() : $this->exactly(3);

        $this->setPassword(
            $currentSettings,
            $setPasswordInvocations,
            new \PHPUnit_Framework_MockObject_Matcher_ConsecutiveParameters([
                [$clearCurrentPassword],
                [$clearNewPassword]
            ])
        );

        $this->decodePassword($encodedCurrentPassword, $clearCurrentPassword);
        $this->getUsername($newSettings, $username);
        $this->setUsername($currentSettings, $username);

        $settingsId = $alreadyExists ? 42 : null;
        $this->getId($currentSettings, $settingsId);

        $persistInvocations = $alreadyExists ? $this->never() : $this->once();
        $this->persist($persistInvocations);
        $this->flushEntityManager();

        $this->trackerSettings->updateSettings($newSettings, $this->user);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getSettingsEntity()
    {
        return $this
            ->getMockBuilder('\Martial\Warez\Settings\Entity\TrackerSettingsEntity')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $repo
     */
    private function getRepository(\PHPUnit_Framework_MockObject_MockObject $repo)
    {
        $this
            ->em
            ->expects($this->once())
            ->method('getRepository')
            ->with('\Martial\Warez\Settings\Entity\TrackerSettingsEntity')
            ->willReturn($repo);
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $repo
     * @param \PHPUnit_Framework_MockObject_MockObject|null $settings
     */
    private function findOneByUser(
        \PHPUnit_Framework_MockObject_MockObject $repo,
        \PHPUnit_Framework_MockObject_MockObject $settings = null
    ) {
        $repo
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['user' => $this->user])
            ->willReturn($settings);
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $settings
     * @param \PHPUnit_Framework_MockObject_Matcher_Invocation $invocations
     * @param \PHPUnit_Framework_MockObject_Stub $stub
     */
    private function getPassword(
        \PHPUnit_Framework_MockObject_MockObject $settings,
        \PHPUnit_Framework_MockObject_Matcher_Invocation $invocations,
        \PHPUnit_Framework_MockObject_Stub $stub
    ) {
        $settings
            ->expects($invocations)
            ->method('getPassword')
            ->will($stub);
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $settings
     * @param \PHPUnit_Framework_MockObject_Matcher_Invocation $invocations
     * @param \PHPUnit_Framework_MockObject_Matcher_StatelessInvocation $params
     */
    private function setPassword(
        \PHPUnit_Framework_MockObject_MockObject $settings,
        \PHPUnit_Framework_MockObject_Matcher_Invocation $invocations,
        \PHPUnit_Framework_MockObject_Matcher_StatelessInvocation $params
    ) {
        $settings
            ->expects($invocations)
            ->method('setPassword')
            ->getMatcher()
            ->parametersMatcher = $params;
    }

    /**
     * @param string $encodedPassword
     * @param string $clearPassword
     */
    private function decodePassword($encodedPassword, $clearPassword)
    {
        $this
            ->encoder
            ->expects($this->once())
            ->method('decode')
            ->with($encodedPassword)
            ->willReturn($clearPassword);
    }

    /**
     * @param string $clearPassword
     * @param string $encodedPassword
     */
    private function encodePassword($clearPassword, $encodedPassword)
    {
        $this
            ->encoder
            ->expects($this->once())
            ->method('encode')
            ->with($clearPassword)
            ->willReturn($encodedPassword);
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $settings
     * @param string $username
     */
    private function getUsername(\PHPUnit_Framework_MockObject_MockObject $settings, $username)
    {
        $settings
            ->expects($this->once())
            ->method('getUsername')
            ->willReturn($username);
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $settings
     * @param string $username
     */
    private function setUsername(\PHPUnit_Framework_MockObject_MockObject $settings, $username)
    {
        $settings
            ->expects($this->once())
            ->method('setUsername')
            ->with($username);
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $settings
     * @param string $id
     */
    private function getId(\PHPUnit_Framework_MockObject_MockObject $settings, $id)
    {
        $settings
            ->expects($this->once())
            ->method('getId')
            ->willReturn($id);
    }

    private function persist(\PHPUnit_Framework_MockObject_Matcher_Invocation $invocations)
    {
        $this
            ->em
            ->expects($invocations)
            ->method('persist');
    }

    private function flushEntityManager()
    {
        $this
            ->em
            ->expects($this->once())
            ->method('flush');
    }
}

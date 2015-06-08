<?php

namespace Martial\Warez\Tests\Settings;

use Martial\Warez\Settings\Entity\FreeboxSettingsEntity;
use Martial\Warez\Settings\FreeboxSettings;

class FreeboxSettingsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $em;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $user;

    /**
     * @var FreeboxSettings
     */
    public $settingsManager;

    protected function setUp()
    {
        $this->em = $this->getMock('\Doctrine\ORM\EntityManagerInterface');
        $this->settingsManager = new FreeboxSettings($this->em);

        $this->user = $this
            ->getMockBuilder('\Martial\Warez\User\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetExistingSettingsShouldReturnTheseSettings()
    {
        $settingsEntity = new FreeboxSettingsEntity();
        $repo = $this->getRepository();
        $this->findOneBy($repo, ['user' => $this->user], $settingsEntity);

        $result = $this->settingsManager->getSettings($this->user);
        $this->assertSame($settingsEntity, $result);
    }

    public function testGetNonExistingSettingsShouldReturnEmptySettings()
    {
        $repo = $this->getRepository();
        $this->findOneBy($repo, ['user' => $this->user], null);

        $result = $this->settingsManager->getSettings($this->user);
        $this->assertInstanceOf('\Martial\Warez\Settings\Entity\FreeboxSettingsEntity', $result);
        $this->assertSame($this->user, $result->getUser());
    }

    public function testUpdateExistingSettingsShouldNotCallThePersistMethodOfTheEntityManager()
    {
        $newSettings = $this->getSettingsEntity();
        $currentSettings = $this->getSettingsEntity();
        $this->updateSettings($currentSettings, $newSettings);

        $this->settingsManager->updateSettings($newSettings, $this->user);
    }

    public function testUpdateNewSettingsShouldCallPersistMethodOfTheEntityManager()
    {
        $newSettings = $this->getSettingsEntity();
        $currentSettings = $this->getSettingsEntity();
        $this->updateSettings($currentSettings, $newSettings, false);

        $this->settingsManager->updateSettings($newSettings, $this->user);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getRepository()
    {
        $repo = $this->getMock('\Doctrine\Common\Persistence\ObjectRepository');

        $this
            ->em
            ->expects($this->once())
            ->method('getRepository')
            ->with('\Martial\Warez\Settings\Entity\FreeboxSettingsEntity')
            ->willReturn($repo);

        return $repo;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $repo
     * @param array $criteria
     * @param \PHPUnit_Framework_MockObject_MockObject|null $return
     */
    private function findOneBy(\PHPUnit_Framework_MockObject_MockObject $repo, array $criteria, $return)
    {
        $repo
            ->expects($this->once())
            ->method('findOneBy')
            ->with($criteria)
            ->willReturn($return);
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject|null $return
     */
    private function getSettings($return)
    {
        $repo = $this->getRepository();
        $this->findOneBy($repo, ['user' => $this->user], $return);
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $currentSettings
     * @param \PHPUnit_Framework_MockObject_MockObject $newSettings
     * @param string $methodSuffix
     * @param mixed $value
     */
    private function updateSettingsProperty(
        \PHPUnit_Framework_MockObject_MockObject $currentSettings,
        \PHPUnit_Framework_MockObject_MockObject $newSettings,
        $methodSuffix,
        $value
    ) {
        $newSettings
            ->expects($this->once())
            ->method('get' . $methodSuffix)
            ->willReturn($value);

        $currentSettings
            ->expects($this->once())
            ->method('set' . $methodSuffix)
            ->with($value)
            ->willReturnSelf();
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $currentSettings
     * @param \PHPUnit_Framework_MockObject_MockObject $newSettings
     */
    private function updateCurrentSettings(
        \PHPUnit_Framework_MockObject_MockObject $currentSettings,
        \PHPUnit_Framework_MockObject_MockObject $newSettings
    ) {
        $this->updateSettingsProperty($currentSettings, $newSettings, 'SessionToken', uniqid());
        $this->updateSettingsProperty($currentSettings, $newSettings, 'AppId', uniqid());
        $this->updateSettingsProperty($currentSettings, $newSettings, 'AppName', 'My App');
        $this->updateSettingsProperty($currentSettings, $newSettings, 'AppVersion', '1.2.3');
        $this->updateSettingsProperty($currentSettings, $newSettings, 'DeviceName', 'My seedbox');
        $this->updateSettingsProperty($currentSettings, $newSettings, 'TransportHost', '42.42.42.42');
        $this->updateSettingsProperty($currentSettings, $newSettings, 'TransportPort', '8080');
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
     * @param \PHPUnit_Framework_MockObject_MockObject $currentSettings
     * @param \PHPUnit_Framework_MockObject_MockObject $newSettings
     * @param bool $alreadyExists
     */
    private function updateSettings(
        \PHPUnit_Framework_MockObject_MockObject $currentSettings,
        \PHPUnit_Framework_MockObject_MockObject $newSettings,
        $alreadyExists = true
    ) {
        $this->getSettings($currentSettings);
        $this->updateCurrentSettings($currentSettings, $newSettings);
        $settingsId = $alreadyExists ? 45651 : null;

        $currentSettings
            ->expects($this->once())
            ->method('getId')
            ->willReturn($settingsId);

        $persistCalls = $alreadyExists ? $this->never() : $this->once();

        $this
            ->em
            ->expects($persistCalls)
            ->method('persist')
            ->with($currentSettings);

        $this
            ->em
            ->expects($this->once())
            ->method('flush');
    }
}

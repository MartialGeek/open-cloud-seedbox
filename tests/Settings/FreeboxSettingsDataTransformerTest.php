<?php

namespace Martial\Warez\Tests\Settings;

use Martial\Warez\Settings\Entity\FreeboxSettingsEntity;
use Martial\Warez\Settings\FreeboxSettingsDataTransformer;

class FreeboxSettingsDataTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testToArray()
    {
        $transformer = new FreeboxSettingsDataTransformer();
        $toArray = $this->getArrayData();
        $entity = new FreeboxSettingsEntity();

        $entity
            ->setTransportHost($toArray['transportHost'])
            ->setTransportPort($toArray['transportPort'])
            ->setAppId($toArray['appId'])
            ->setAppName($toArray['appName'])
            ->setAppVersion($toArray['appVersion'])
            ->setDeviceName($toArray['deviceName'])
            ->setSessionToken($toArray['sessionToken'])
            ->setAppToken($toArray['appToken'])
            ->setChallenge($toArray['challenge']);

        $result = $transformer->toArray($entity);
        $this->assertSame($toArray, $result);
    }

    public function testToObject()
    {
        $transformer = new FreeboxSettingsDataTransformer();
        $data = $this->getArrayData();
        $result = $transformer->toObject($data);

        $this->assertSame($data['transportHost'], $result->getTransportHost());
        $this->assertSame($data['transportPort'], $result->getTransportPort());
        $this->assertSame($data['appId'], $result->getAppId());
        $this->assertSame($data['appName'], $result->getAppName());
        $this->assertSame($data['appVersion'], $result->getAppVersion());
        $this->assertSame($data['deviceName'], $result->getDeviceName());
        $this->assertSame($data['sessionToken'], $result->getSessionToken());
        $this->assertSame($data['appToken'], $result->getAppToken());
        $this->assertSame($data['challenge'], $result->getChallenge());
    }

    /**
     * @return array
     */
    private function getArrayData()
    {
        return [
            'transportHost' => '42.42.42.42',
            'transportPort' => 8888,
            'appId' => uniqid(),
            'appName' => 'My app',
            'appVersion' => '1.2.3',
            'deviceName' => 'My seedbox',
            'sessionToken' => uniqid(),
            'appToken' => uniqid(),
            'challenge' => uniqid()
        ];
    }
}

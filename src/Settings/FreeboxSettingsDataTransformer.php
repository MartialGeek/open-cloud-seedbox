<?php

namespace Martial\Warez\Settings;

use Martial\Warez\Settings\Entity\FreeboxSettingsEntity;

class FreeboxSettingsDataTransformer
{
    /**
     * @param FreeboxSettingsEntity $settings
     * @return array
     */
    public function toArray(FreeboxSettingsEntity $settings)
    {
        return [
            'transportHost' => $settings->getTransportHost(),
            'transportPort' => $settings->getTransportPort(),
            'appId' => $settings->getAppId(),
            'appName' => $settings->getAppName(),
            'appVersion' => $settings->getAppVersion(),
            'deviceName' => $settings->getDeviceName(),
            'sessionToken' => $settings->getSessionToken(),
            'appToken' => $settings->getAppToken(),
            'challenge' => $settings->getChallenge()
        ];
    }

    /**
     * @param array $array
     * @return FreeboxSettingsEntity
     */
    public function toObject(array $array)
    {
        $settings = new FreeboxSettingsEntity();

        $settings->setTransportHost($array['transportHost']);
        $settings->setTransportPort($array['transportPort']);
        $settings->setAppId($array['appId']);
        $settings->setAppName($array['appName']);
        $settings->setAppVersion($array['appVersion']);
        $settings->setDeviceName($array['deviceName']);
        $settings->setSessionToken($array['sessionToken']);
        $settings->setAppToken($array['appToken']);
        $settings->setChallenge($array['challenge']);

        return $settings;
    }
}

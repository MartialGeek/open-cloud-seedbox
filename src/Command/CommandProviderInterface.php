<?php

namespace Martial\OpenCloudSeedbox\Command;

use Silex\Application as Silex;

interface CommandProviderInterface
{
    /**
     * @param Silex $app
     * @param array $config
     */
    public function registerCommands(Silex $app, array $config = []);
}

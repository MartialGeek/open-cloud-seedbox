<?php

namespace Martial\Warez\Command;

use Silex\Application as Silex;

interface CommandProviderInterface
{
    /**
     * @param Silex $app
     * @param array $config
     */
    public function registerCommands(Silex $app, array $config = []);
}

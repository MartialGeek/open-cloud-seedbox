<?php

namespace Martial\Warez\Command;

use Martial\Warez\Command\Assets\AssetsInstall;
use Silex\Application as SilexApplication;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Filesystem\Filesystem;

class Application extends BaseApplication
{
    /**
     * @var SilexApplication
     */
    private $app;

    /**
     * @var array
     */
    private $config;

    public function __construct(SilexApplication $application, array $config = array())
    {
        parent::__construct('Warez console', '0.0.0');
        $this->app = $application;
        $this->config = $config;
        $this->registerCommands();
    }

    protected function registerCommands()
    {
        if (!isset($this->config['assets'])) {
            throw new \InvalidArgumentException('Missing assets configuration.');
        }

        if (!isset($this->config['assets']['source_paths'])) {
            throw new \InvalidArgumentException('The source_paths key is required in the assets configuration.');
        }

        if (!isset($this->config['assets']['destination_path'])) {
            throw new \InvalidArgumentException('The destination_path key is required in the assets configuration.');
        }

        $this->add(new AssetsInstall(
            new Filesystem(),
            $this->config['assets']['source_paths'],
            $this->config['assets']['destination_path']
        ));
    }
}
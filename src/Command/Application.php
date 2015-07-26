<?php

namespace Martial\OpenCloudSeedbox\Command;

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Martial\OpenCloudSeedbox\Command\Message\Listen;
use Martial\OpenCloudSeedbox\Command\User\UserCreate;
use Martial\OpenCloudSeedbox\Command\Assets\AssetsInstall;
use Martial\OpenCloudSeedbox\Command\Server\ServerRun;
use Silex\Application as SilexApplication;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\ProcessBuilder;

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

    /**
     * @todo Extract the validation of the configuration in a separate component.
     * @param CommandProviderInterface[] $commandProviders
     */
    public function registerCommands(array $commandProviders = [])
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

        $this->setHelperSet(new HelperSet([
            'em' => new EntityManagerHelper($this->app['doctrine.entity_manager']),
        ]));

        ConsoleRunner::addCommands($this);

        foreach ($commandProviders as $provider) {
            if (!is_object($provider)) {
                throw new \InvalidArgumentException(sprintf(
                    'The arguments passed to the method %s must implement \Martial\OpenCloudSeedbox\Command\CommandProviderInterface',
                    __CLASS__ . '::' . __METHOD__
                ));
            }

            if (!($provider instanceof CommandProviderInterface)) {
                throw new \InvalidArgumentException(sprintf(
                    'The class %s does not implement \Martial\OpenCloudSeedbox\Command\CommandProviderInterface',
                    get_class($provider)
                ));
            }

            $provider->registerCommands($this->app, $this->config);
        }

        $this->addCommands([
            new AssetsInstall(
                new Filesystem(),
                $this->config['assets']['source_paths'],
                $this->config['assets']['destination_path']
            ),
            new ServerRun(new ProcessBuilder(), $this->config['project_root']),
            new UserCreate($this->app['user.service']),
            new Listen(
                $this->app['message_queuing.freebox.consumer'],
                $this->app['doctrine.entity_manager']->getConnection()
            ),
        ]);
    }
}

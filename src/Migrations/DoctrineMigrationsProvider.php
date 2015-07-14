<?php

namespace Martial\Warez\Migrations;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Tools\Console\Command\AbstractCommand;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Kurl\Silex\Provider\DoctrineMigrationsProvider as KurlDoctrineMigrationsProvider;
use Martial\Warez\Command\CommandProviderInterface;
use Silex\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;

class DoctrineMigrationsProvider extends KurlDoctrineMigrationsProvider implements CommandProviderInterface
{
    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     *
     * @param Application $app
     */
    public function boot(Application $app)
    {
        $helperSet = new HelperSet(array(
            'connection' => new ConnectionHelper($app['db']),
            'dialog'     => new QuestionHelper(),
            'em'         => new EntityManagerHelper($app['doctrine.entity_manager'])
        ));

        $this->console->setHelperSet($helperSet);

        $commands = array(
            'Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand',
            'Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand',
            'Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand',
            'Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand',
            'Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand',
            'Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand'
        );

        $configuration = new Configuration($app['db'], $app['migrations.output_writer']);

        $configuration->setMigrationsDirectory($app['migrations.directory']);
        $configuration->setName($app['migrations.name']);
        $configuration->setMigrationsNamespace($app['migrations.namespace']);
        $configuration->setMigrationsTableName($app['migrations.table_name']);
        $configuration->registerMigrationsFromDirectory($app['migrations.directory']);

        foreach ($commands as $name) {
            /** @var AbstractCommand $command */
            $command = new $name();
            $command->setMigrationConfiguration($configuration);
            $this->console->add($command);
        }
    }

    /**
     * @param Application $app
     * @param array $config
     */
    public function registerCommands(Application $app, array $config = [])
    {
        $provider = new self($app['console']);
        $app->register($provider, $config['doctrine']['migrations']);
        $provider->boot($app);
    }
}

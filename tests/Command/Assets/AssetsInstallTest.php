<?php

namespace Martial\Warez\Tests\Command\Assets;

use Martial\Warez\Command\Assets\AssetsInstall;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class AssetsInstallTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateSymlinks()
    {
        $config = include __DIR__ . '/../mockConsoleConfig.php';
        $console = new Application();
        $fs = $this
            ->getMockBuilder('\Symfony\Component\Filesystem\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();

        $console->add(new AssetsInstall($fs, $config['assets']['source_paths'], $config['assets']['destination_path']));
        $command = $console->find('assets:install');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);
        $this->assertSame(0, $commandTester->getStatusCode());
    }
}

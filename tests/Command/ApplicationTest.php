<?php

namespace Martial\Warez\Tests\Command;

use Martial\Warez\Command\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $silexApp;

    /**
     * @var array
     */
    public $config;

    /**
     * @var Application
     */
    public $console;

    public function testHelpCommandShouldReturnZeroAsExitStatus()
    {
        $command = $this->console->find('help');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);
        $this->assertTrue(
            0 === $commandTester->getStatusCode(),
            'The console app returned an exit status different from 0.'
        );
    }

    public function testList()
    {
        $command = $this->console->find('list');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);
        $this->assertRegExp('/assets:install/', $commandTester->getDisplay(), 'Command assets:install was not found.');
    }

    protected function setUp()
    {
        $this->silexApp = $this
            ->getMockBuilder('\Silex\Application')
            ->disableOriginalConstructor()
            ->getMock();

        $this->config = include __DIR__ . '/mockConsoleConfig.php';
        $this->console = new Application($this->silexApp, $this->config);
    }
}

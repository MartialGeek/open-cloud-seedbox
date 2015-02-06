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

    protected function setUp()
    {
        $this->silexApp = $this
            ->getMockBuilder('\Silex\Application')
            ->disableOriginalConstructor()
            ->getMock();

        $this->console = new Application($this->silexApp);
    }
}

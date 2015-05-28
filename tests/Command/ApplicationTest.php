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
     * @var \PHPUnit_Framework_MockObject_MockObject[]
     */
    public $services;

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
        $this->assertRegExp('/server:run/', $commandTester->getDisplay(), 'Command server:run was not found.');
        $this->assertRegExp('/user:create/', $commandTester->getDisplay(), 'Command server:run was not found.');
        $this->assertRegExp('/message:listen/', $commandTester->getDisplay(), 'Command server:run was not found.');
    }

    protected function setUp()
    {
        $freeboxConsumer = $this
            ->getMockBuilder('\Martial\Warez\MessageQueuing\Freebox\FreeboxMessageConsumer')
            ->disableOriginalConstructor()
            ->getMock();

        $entityManager = $this->getMock('\Doctrine\ORM\EntityManagerInterface');

        $connection = $this
            ->getMockBuilder('\Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $entityManager
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);


        $services = [
            'user.service' => $this->getMock('\Martial\Warez\User\UserServiceInterface'),
            'message_queuing.freebox.consumer' => $freeboxConsumer,
            'doctrine.entity_manager' => $entityManager
        ];

        $this->services = $services;

        $this->silexApp = $this
            ->getMockBuilder('\Silex\Application')
            ->setMethods(['offsetGet'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->silexApp
            ->expects($this->any())
            ->method('offsetGet')
            ->will($this->returnCallback(
                function($key) use($services) {
                    return $services[$key];
                }
            ));

        $this->config = include __DIR__ . '/mockConsoleConfig.php';
        $this->console = new Application($this->silexApp, $this->config);
    }
}

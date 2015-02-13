<?php

namespace Martial\Warez\Tests\Command\Server;


use Martial\Warez\Command\Server\ServerRun;
use Symfony\Component\Console\Tester\CommandTester;

class ServerRunTest extends \PHPUnit_Framework_TestCase
{
    public function testEmbeddedServer()
    {
        $config = require __DIR__ . '/../mockConsoleConfig.php';
        $projectRoot = $config['project_root'];
        $host = sprintf('%s:%d', '127.0.0.1', 8888);
        $documentRoot = $projectRoot . DIRECTORY_SEPARATOR . 'web';
        $processOptions = [PHP_BINARY, '-S', $host, '-t', $documentRoot];

        $processBuilder = $this
            ->getMockBuilder('\Symfony\Component\Process\ProcessBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $process = $this
            ->getMockBuilder('\Symfony\Component\Process\Process')
            ->disableOriginalConstructor()
            ->getMock();

        $processBuilder
            ->expects($this->once())
            ->method('setArguments')
            ->with($this->equalTo($processOptions))
            ->will($this->returnValue($processBuilder));

        $processBuilder
            ->expects($this->once())
            ->method('setTimeout')
            ->with($this->equalTo(null))
            ->will($this->returnValue($processBuilder));

        $processBuilder
            ->expects($this->once())
            ->method('getProcess')
            ->will($this->returnValue($process));

        $process
            ->expects($this->once())
            ->method('run');

        $process
            ->expects($this->once())
            ->method('getExitCode');

        $command = new ServerRun($processBuilder, $projectRoot);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
    }
}

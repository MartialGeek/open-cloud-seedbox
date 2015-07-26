<?php

namespace Martial\OpenCloudSeedbox\Tests\Command\Assets;

use Martial\OpenCloudSeedbox\Command\Assets\AssetsInstall;
use Symfony\Component\Console\Tester\CommandTester;

class AssetsInstallTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateSymlinks()
    {
        $config = include __DIR__ . '/../mockConsoleConfig.php';

        $fs = $this
            ->getMockBuilder('\Symfony\Component\Filesystem\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();

        $fsCallsCount = count($config['assets']['source_paths']);

        $makePathRelativeArgs = [];
        $makePathRelativeResults = [];
        $symlinkArgs = [];

        foreach ($config['assets']['source_paths'] as $index => $path) {
            $makePathRelativeArgs[] = [
                $this->equalTo($path),
                $this->equalTo($config['assets']['destination_path']),
            ];

            $makePathRelativeResults[] = '../relative/path/' . $index;

            $symlinkArgs[] = [
                $this->equalTo('../relative/path/' . $index),
                $this->equalTo(
                    $config['assets']['destination_path'] . substr($path, strrpos($path, DIRECTORY_SEPARATOR))
                )
            ];
        }

        $makePathRelativeInvocation = $fs
            ->expects($this->exactly($fsCallsCount))
            ->method('makePathRelative');

        $makePathRelativeInvocation
            ->getMatcher()
            ->parametersMatcher = new \PHPUnit_Framework_MockObject_Matcher_ConsecutiveParameters(
                $makePathRelativeArgs
            );

        $makePathRelativeInvocation->will(
            new \PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls($makePathRelativeResults)
        );

        $symlinkInvocation = $fs
            ->expects($this->exactly($fsCallsCount))
            ->method('symlink');

        $symlinkInvocation
            ->getMatcher()
            ->parametersMatcher = new \PHPUnit_Framework_MockObject_Matcher_ConsecutiveParameters($symlinkArgs);

        $command = new AssetsInstall($fs, $config['assets']['source_paths'], $config['assets']['destination_path']);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $this->assertSame(0, $commandTester->getStatusCode());
    }
}

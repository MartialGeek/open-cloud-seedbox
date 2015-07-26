<?php

namespace Martial\OpenCloudSeedbox\Tests\Filesystem;

use Martial\OpenCloudSeedbox\Filesystem\ZipArchiver;

class ZipArchiverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ZipArchiver
     */
    public $archiver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $zippy;

    protected function setUp()
    {
        $this->zippy = $this
            ->getMockBuilder('\Alchemy\Zippy\Zippy')
            ->disableOriginalConstructor()
            ->getMock();

        $this->archiver = new ZipArchiver($this->zippy);
    }

    public function testCreateArchiveOfFile()
    {
        $this->createArchive();
    }

    public function testCreateArchiveOfDir()
    {
        $this->createArchive('dir');
    }

    private function createArchive($type = 'file')
    {
        $filename = 'file.txt';
        $filePath = '/path/to/file.txt';
        $archivePath = '/path/to/archives';

        if ($type == 'file') {
            $structure = [$filePath];
        } else {
            $structure = [$filename => $filePath];
        }

        $fileConstructorArg = $type == 'file' ? $filePath : $archivePath;

        $file = $this
            ->getMockBuilder('\SplFileInfo')
            ->setConstructorArgs([$fileConstructorArg])
            ->getMock();

        $file
            ->expects($this->once())
            ->method('isDir')
            ->willReturn($type == 'dir');

        $file
            ->expects($this->once())
            ->method('getRealPath')
            ->willReturn($filePath);

        if ($type == 'dir') {
            $file
                ->expects($this->once())
                ->method('getFilename')
                ->willReturn($filename);
        }

        $this
            ->zippy
            ->expects($this->once())
            ->method('create')
            ->with($this->equalTo($archivePath), $this->equalTo($structure));

        $this->archiver->createArchive($file, $archivePath);
    }
}

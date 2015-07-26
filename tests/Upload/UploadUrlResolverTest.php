<?php

namespace Martial\OpenCloudSeedbox\Tests\Upload;

use Martial\OpenCloudSeedbox\Upload\UploadUrlResolver;

class UploadUrlResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $file;

    /**
     * @var string
     */
    public $filePath;

    /**
     * @var string
     */
    public $host;

    /**
     * @var UploadUrlResolver
     */
    public $resolver;

    protected function setUp()
    {
        $this->host = 'http://warez.dev';
        $this->resolver = new UploadUrlResolver();
        $this->resolver->setHost($this->host);
        $this->filePath = '/path/to/file.txt';
        $this->file = $this
            ->getMockBuilder('\Symfony\Component\HttpFoundation\File\File')
            ->setConstructorArgs([
                $this->filePath,
                false
            ])
            ->getMock();
    }

    public function testResolveRegular()
    {
        $this->resolve();
    }

    public function testResolveArchive()
    {
        $this->resolve('archive');
    }

    public function testResolveUnknownType()
    {
        $this->resolve('unknown');
    }

    private function resolve($type = 'regular')
    {
        switch ($type) {
            case 'archive':
                $uploadType = $type;
                break;
            default:
                $uploadType = 'regular';
        }

        $filePathName = dirname($this->filePath);

        $this
            ->file
            ->expects($this->once())
            ->method('getPathname')
            ->willReturn($filePathName);

        $url = $this->resolver->resolve($this->file, ['upload_type' => $uploadType]);
        $expectedUrl = $this->host . '/upload/?filename=' . urlencode($filePathName) . '&upload-type=' . $uploadType;
        $this->assertSame($expectedUrl, $url);
    }
}

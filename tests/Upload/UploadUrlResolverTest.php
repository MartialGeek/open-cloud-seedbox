<?php

namespace Martial\Warez\Tests\Upload;

use Martial\Warez\Upload\UploadUrlResolver;

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

    public function testResolve()
    {
        $filePathName = dirname($this->filePath);

        $this
            ->file
            ->expects($this->once())
            ->method('getPathname')
            ->willReturn($filePathName);

        $url = $this->resolver->resolve($this->file);
        $expectedUrl = $this->host . '/upload/?filename=' . urlencode($filePathName);
        $this->assertSame($expectedUrl, $url);
    }
}

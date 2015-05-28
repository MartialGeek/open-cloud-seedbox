<?php

namespace Martial\Warez\Tests\Upload;

use Martial\Warez\Upload\UploadListener;

class UploadListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UploadListener
     */
    public $listener;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $event;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $requestAttributes;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $query;

    /**
     * @var string
     */
    public $filename;

    protected function setUp()
    {
        $this->listener = new UploadListener();

        $this->event = $this
            ->getMockBuilder('\Symfony\Component\HttpKernel\Event\PostResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this
            ->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestAttributes = $this
            ->getMockBuilder('\Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();

        $this->query = $this
            ->getMockBuilder('\Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request->attributes = $this->requestAttributes;
        $this->request->query = $this->query;
        $this->filename = '/tmp/' . uniqid();
    }

    protected function tearDown()
    {
        if (file_exists($this->filename)) {
            unlink($this->filename);
        }
    }

    public function testListenDoesNotMatchTheUploadRoute()
    {
        $this->listen('a_route');
    }

    public function testListenMatchesTheUploadRouteWithARegularUpload()
    {
        $this->listen('upload_file');
    }

    public function testListenMatchesTheUploadRouteWithArchiveType()
    {
        $this->listen('upload_file', 'archive');
    }

    private function listen($route, $uploadType = 'regular')
    {
        $this
            ->requestAttributes
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('_route'))
            ->willReturn($route);

        $this
            ->event
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->request);

        if ($route == 'upload_file') {
            $getCalls = $uploadType == 'regular' ? $this->once() : $this->exactly(2);

            $getParams = [$this->equalTo('upload-type')];

            if ($uploadType == 'archive') {
                $getParams[] = $this->equalTo('filename');
                $file = fopen($this->filename, 'w');
                fclose($file);
            }

            $invocation = $this
                ->query
                ->expects($getCalls)
                ->method('get');

            $invocation
                ->getMatcher()
                ->parametersMatcher = new \PHPUnit_Framework_MockObject_Matcher_ConsecutiveParameters($getParams);

            $invocation->willReturnOnConsecutiveCalls($uploadType, $this->filename);
        }

        $this->listener->onKernelTerminate($this->event);
    }
}

<?php

namespace Martial\OpenCloudSeedbox\Tests\MessageQueuing;

abstract class AbstractMessageQueuing extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $connection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $channel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $logger;

    protected function setUp()
    {
        $this->connection = $this
            ->getMockBuilder('\PhpAmqpLib\Connection\AMQPStreamConnection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->channel = $this
            ->getMockBuilder('\PhpAmqpLib\Channel\AMQPChannel')
            ->disableOriginalConstructor()
            ->getMock();

        $this
            ->connection
            ->expects($this->once())
            ->method('channel')
            ->with($this->equalTo(null))
            ->willReturn($this->channel);

        $this->logger = $this->getMock('\Psr\Log\LoggerInterface');
    }

    protected function tearDown()
    {
        $this
            ->channel
            ->expects($this->once())
            ->method('close');

        $this
            ->connection
            ->expects($this->once())
            ->method('close');

        unset($this->messageProducer);
    }
}

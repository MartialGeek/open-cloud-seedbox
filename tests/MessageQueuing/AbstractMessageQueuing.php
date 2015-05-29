<?php

namespace Martial\Warez\Tests\MessageQueuing;

use Martial\Warez\MessageQueuing\Freebox\FreeboxMessageProducer;

abstract class AbstractMessageQueuing extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FreeboxMessageProducer
     */
    public $messageProducer;

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
        $this->messageProducer = new FreeboxMessageProducer($this->connection);
        $this->messageProducer->setLogger($this->logger);
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

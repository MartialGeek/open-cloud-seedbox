<?php

namespace Martial\Warez\Tests\MessageQueuing;

use Martial\Warez\MessageQueuing\Freebox\FreeboxMessageProducer;

class FreeboxMessageProducerTest extends \PHPUnit_Framework_TestCase
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

        $this->messageProducer = new FreeboxMessageProducer($this->connection);
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

    public function testGenerateArchiveAndUpload()
    {
        $fileName = 'superfilename.txt';
        $userId = 42;
        $queue = 'warez.freebox.generate_archive_and_upload';

        $this
            ->channel
            ->expects($this->once())
            ->method('queue_declare')
            ->with(
                $this->equalTo($queue),
                $this->equalTo(false),
                $this->equalTo(false),
                $this->equalTo(false),
                $this->equalTo(false)
            );

        $this
            ->channel
            ->expects($this->once())
            ->method('basic_publish')
            ->with(
                $this->isInstanceOf('\PhpAmqpLib\Message\AMQPMessage'),
                $this->equalTo(''),
                $this->equalTo($queue)
            );

        $this->messageProducer->generateArchiveAndUpload($fileName, $userId);
    }
}

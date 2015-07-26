<?php

namespace Martial\OpenCloudSeedbox\Tests\MessageQueuing;

use Martial\OpenCloudSeedbox\MessageQueuing\Freebox\FreeboxMessageProducer;

class FreeboxMessageProducerTest extends AbstractMessageQueuing
{
    /**
     * @var FreeboxMessageProducer
     */
    public $messageProducer;

    public function testGenerateArchiveAndUpload()
    {
        $this->messageProducer = new FreeboxMessageProducer($this->connection);
        $this->messageProducer->setLogger($this->logger);

        $fileName = 'superfilename.txt';
        $userId = 42;
        $queue = 'ocs.freebox.generate_archive_and_upload';

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

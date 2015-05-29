<?php

namespace Martial\Warez\Tests\MessageQueuing;

class FreeboxMessageProducerTest extends AbstractMessageQueuing
{
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

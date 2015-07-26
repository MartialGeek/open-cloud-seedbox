<?php

namespace Martial\OpenCloudSeedbox\MessageQueuing\Freebox;

use Martial\OpenCloudSeedbox\MessageQueuing\AbstractMessageQueuing;
use PhpAmqpLib\Message\AMQPMessage;

class FreeboxMessageProducer extends AbstractMessageQueuing
{
    public function generateArchiveAndUpload($fileName, $userId)
    {
        $body = json_encode([
            'fileName' => $fileName,
            'userId' => $userId
        ], JSON_FORCE_OBJECT);

        $queue = FreeboxQueues::GENERATE_ARCHIVE_AND_UPLOAD;
        $this->channel->queue_declare($queue, false, false, false, false);
        $this->channel->basic_publish(new AMQPMessage($body), '', $queue);
    }
}

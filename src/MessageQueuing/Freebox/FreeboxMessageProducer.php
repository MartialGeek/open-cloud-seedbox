<?php

namespace Martial\Warez\MessageQueuing\Freebox;

use Martial\Warez\MessageQueuing\AbstractMessageQueuing;
use PhpAmqpLib\Message\AMQPMessage;

class FreeboxMessageProducer extends AbstractMessageQueuing
{
    public function generateArchiveAndUpload($fileName, $userId)
    {
        $body = json_encode([
            'fileName' => $fileName,
            'userId' => $userId
        ], JSON_FORCE_OBJECT);

        $this->channel->queue_declare(FreeboxQueues::GENERATE_ARCHIVE_AND_UPLOAD, false, false, false, false);
        $this->channel->basic_publish(new AMQPMessage($body));
    }
}

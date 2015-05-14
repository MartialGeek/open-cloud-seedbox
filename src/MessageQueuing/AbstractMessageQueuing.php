<?php

namespace Martial\Warez\MessageQueuing;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Psr\Log\LoggerInterface;

abstract class AbstractMessageQueuing
{
    /**
     * @var AMQPStreamConnection
     */
    protected $connection;

    /**
     * @var AMQPChannel
     */
    protected $channel;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param AMQPStreamConnection $connection
     * @param string $channelId
     */
    public function __construct(AMQPStreamConnection $connection, $channelId = null)
    {
        $this->connection = $connection;
        $this->channel = $this->connection->channel($channelId);
    }

    function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}

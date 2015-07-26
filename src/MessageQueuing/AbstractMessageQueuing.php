<?php

namespace Martial\OpenCloudSeedbox\MessageQueuing;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Psr\Log\LoggerInterface;

abstract class AbstractMessageQueuing
{
    /**
     * @var AMQPStreamConnection
     */
    protected $brokerConnection;

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
        $this->brokerConnection = $connection;
        $this->channel = $this->brokerConnection->channel($channelId);
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->brokerConnection->close();
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}

<?php

namespace Martial\Warez\MessageQueuing\Freebox;

use Doctrine\DBAL\Connection;
use Martial\Warez\MessageQueuing\AbstractMessageQueuing;
use Martial\Warez\Upload\Freebox\FreeboxManager;
use Martial\Warez\User\UserServiceInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Console\Output\OutputInterface;

class FreeboxMessageConsumer extends AbstractMessageQueuing
{
    /**
     * @var UserServiceInterface
     */
    private $userService;

    /**
     * @var FreeboxManager
     */
    private $freeboxManager;

    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @param UserServiceInterface $userService
     */
    public function setUserService(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @param FreeboxManager $freeboxManager
     */
    public function setFreeboxManager(FreeboxManager $freeboxManager)
    {
        $this->freeboxManager = $freeboxManager;
    }

    /**
     * Listens the messages that generate the archives and then upload them on the Freebox.
     *
     * @param Connection $connection
     * @param OutputInterface $output
     */
    public function generateArchiveAndUpload(Connection $connection, OutputInterface $output)
    {
        $this->dbalConnection = $connection;
        $this->output = $output;
        $queue = FreeboxQueues::GENERATE_ARCHIVE_AND_UPLOAD;
        $this->channel->queue_declare($queue, false, false, false, false);
        $this->channel->basic_consume(
            $queue,
            '',
            false,
            true,
            false,
            false,
            [$this, 'consumeGenerateArchiveAndUploadMessage']
        );

        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }

    public function consumeGenerateArchiveAndUploadMessage(AMQPMessage $message)
    {
        if ($this->logger) {
            $this->logger->info('New message received: ' . $message->body);
        }

        $this->output->writeln('New message received: ' . $message->body);
        $data = json_decode($message->body, true);
        $this->dbalConnection->connect();
        $user = $this->userService->find($data['userId']);

        if (!$user) {
            $this->dbalConnection->close();
            throw new \InvalidArgumentException('The user with the ID ' . $data['userId'] . ' could not be found.');
        }

        $this->freeboxManager->generateArchiveAndUpload($data['fileName'], $user);
        $this->dbalConnection->close();
    }
}

<?php

namespace Martial\Warez\MessageQueuing\Freebox;

use Martial\Warez\MessageQueuing\AbstractMessageQueuing;
use Martial\Warez\Upload\Freebox\FreeboxManager;
use Martial\Warez\User\UserService;
use PhpAmqpLib\Message\AMQPMessage;

class FreeboxMessageConsumer extends AbstractMessageQueuing
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var FreeboxManager
     */
    private $freeboxManager;

    /**
     * @param UserService $userService
     */
    public function setUserService(UserService $userService)
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
     */
    public function generateArchiveAndUpload()
    {
        $queue = FreeboxQueues::GENERATE_ARCHIVE_AND_UPLOAD;
        $this->channel->queue_declare($queue, false, false, false, false);
        $this->channel->basic_consume($queue, '', false, true, false, false, function (AMQPMessage $msg) {
            if ($this->logger) {
                $this->logger->info('New message received: ' . $msg->body);
            }

            $data = json_decode($msg->body);
            $user = $this->userService->find($data['userId']);

            if (!$user) {
                throw new \InvalidArgumentException('The user with the ID ' . $data['userId'] . ' could not be found.');
            }

            $this->freeboxManager->uploadFile($data['fileName'], $user);
        });
    }
}

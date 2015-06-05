<?php

namespace Martial\Warez\Tests\MessageQueuing;

use Martial\Warez\MessageQueuing\Freebox\FreeboxMessageConsumer;
use Martial\Warez\MessageQueuing\Freebox\FreeboxQueues;

class FreeboxMessageConsumerTest extends AbstractMessageQueuing
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $freeboxManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $userService;

    /**
     * @var FreeboxMessageConsumer
     */
    public $messageConsumer;

    protected function setUp()
    {
        parent::setUp();

        $this->freeboxManager = $this
            ->getMockBuilder('\Martial\Warez\Upload\Freebox\FreeboxManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->userService = $this->getMock('\Martial\Warez\User\UserServiceInterface');
        $this->messageConsumer = new FreeboxMessageConsumer($this->connection);
        $this->messageConsumer->setLogger($this->logger);
        $this->messageConsumer->setFreeboxManager($this->freeboxManager);
        $this->messageConsumer->setUserService($this->userService);
    }

    public function testGenerateArchiveAndUpload()
    {
        $dbalConnection = $this
            ->getMockBuilder('\Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $consoleOutput = $this->getMock('\Symfony\Component\Console\Output\OutputInterface');

        $queue = FreeboxQueues::GENERATE_ARCHIVE_AND_UPLOAD;

        $this
            ->channel
            ->expects($this->once())
            ->method('queue_declare')
            ->with($queue, false, false, false, false);

        $this
            ->channel
            ->expects($this->once())
            ->method('basic_consume')
            ->with(
                $queue,
                '',
                false,
                true,
                false,
                false,
                $this->isInstanceOf('\Closure')
            );

        $this->messageConsumer->generateArchiveAndUpload($dbalConnection, $consoleOutput);
    }
}

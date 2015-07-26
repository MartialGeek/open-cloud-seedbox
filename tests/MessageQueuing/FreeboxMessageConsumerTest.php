<?php

namespace Martial\OpenCloudSeedbox\Tests\MessageQueuing;

use Martial\OpenCloudSeedbox\MessageQueuing\Freebox\FreeboxMessageConsumer;
use Martial\OpenCloudSeedbox\MessageQueuing\Freebox\FreeboxQueues;

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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $consoleOutput;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $dbalConnection;

    /**
     * @var FreeboxMessageConsumer
     */
    public $messageConsumer;

    protected function setUp()
    {
        parent::setUp();

        $this->freeboxManager = $this
            ->getMockBuilder('\Martial\OpenCloudSeedbox\Upload\Freebox\FreeboxManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->dbalConnection = $this
            ->getMockBuilder('\Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->userService = $this->getMock('\Martial\OpenCloudSeedbox\User\UserServiceInterface');
        $this->messageConsumer = new FreeboxMessageConsumer($this->connection);
        $this->messageConsumer->setLogger($this->logger);
        $this->messageConsumer->setFreeboxManager($this->freeboxManager);
        $this->messageConsumer->setUserService($this->userService);
        $this->consoleOutput = $this->getMock('\Symfony\Component\Console\Output\OutputInterface');
    }

    public function testGenerateArchiveAndUpload()
    {
        $this->generateArchiveAndUpload();
    }

    public function testConsumeGenerateArchiveAndUploadMessage()
    {
        $this->consumeGenerateArchiveAndUploadMessage();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConsumeGenerateArchiveUploadMessageWithNotFoundUser()
    {
        $this->consumeGenerateArchiveAndUploadMessage('user_not_found');
    }

    private function generateArchiveAndUpload()
    {
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
                $this->isType('array')
            );

        $this->messageConsumer->generateArchiveAndUpload($this->dbalConnection, $this->consoleOutput);
    }

    public function consumeGenerateArchiveAndUploadMessage($behavior = '')
    {
        $message = $this
            ->getMockBuilder('\PhpAmqpLib\Message\AMQPMessage')
            ->disableOriginalConstructor()
            ->getMock();

        $message->body = '{"userId":"42","fileName":"/path/to/file.txt"}';
        $loggerMessage = 'New message received: ' . $message->body;

        $this
            ->logger
            ->expects($this->once())
            ->method('info')
            ->with($loggerMessage);

        $this
            ->consoleOutput
            ->expects($this->once())
            ->method('writeln')
            ->with($loggerMessage);

        $data = json_decode($message->body, true);

        $this
            ->dbalConnection
            ->expects($this->once())
            ->method('connect');

        $user = $this
            ->getMockBuilder('\Martial\OpenCloudSeedbox\User\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();

        $this
            ->userService
            ->expects($this->once())
            ->method('find')
            ->with($data['userId'])
            ->willReturn('user_not_found' == $behavior ? null : $user);

        if ($behavior != 'user_not_found') {
            $this
                ->freeboxManager
                ->expects($this->once())
                ->method('generateArchiveAndUpload')
                ->with($data['fileName'], $user);
        }

        $this
            ->dbalConnection
            ->expects($this->once())
            ->method('close');

        $this->generateArchiveAndUpload();
        $this->messageConsumer->consumeGenerateArchiveAndUploadMessage($message);
    }
}

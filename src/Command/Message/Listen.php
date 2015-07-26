<?php

namespace Martial\OpenCloudSeedbox\Command\Message;

use Doctrine\DBAL\Connection;
use Martial\OpenCloudSeedbox\MessageQueuing\Freebox\FreeboxMessageConsumer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Listen extends Command
{
    /**
     * @var FreeboxMessageConsumer
     */
    private $freeboxConsumer;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param FreeboxMessageConsumer $freeboxConsumer
     * @param Connection $connection
     */
    public function __construct(FreeboxMessageConsumer $freeboxConsumer, Connection $connection)
    {
        $this->freeboxConsumer = $freeboxConsumer;
        $this->connection = $connection;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('message:listen')
            ->setDescription('Listen the incoming messages.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->freeboxConsumer->generateArchiveAndUpload($this->connection, $output);
    }
}

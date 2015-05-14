<?php

namespace Martial\Warez\Command\Message;

use Martial\Warez\MessageQueuing\Freebox\FreeboxMessageConsumer;
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
     * @param FreeboxMessageConsumer $freeboxConsumer
     */
    public function __construct(FreeboxMessageConsumer $freeboxConsumer)
    {
        $this->freeboxConsumer = $freeboxConsumer;
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
        $this->freeboxConsumer->generateArchiveAndUpload();
    }
}

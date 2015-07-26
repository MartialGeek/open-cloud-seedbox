<?php

namespace Martial\OpenCloudSeedbox\Command\Server;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;

class ServerRun extends Command
{
    /**
     * @var ProcessBuilder
     */
    private $processBuilder;

    /**
     * @var string
     */
    private $projectRoot;

    /**
     * @param ProcessBuilder $processBuilder
     * @param string $projectRoot   The path of your project directory.
     */
    public function __construct(ProcessBuilder $processBuilder, $projectRoot)
    {
        $this->processBuilder = $processBuilder;
        $this->projectRoot = $projectRoot;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('server:run')
            ->setDescription('Runs the server embedded with your PHP CLI')
            ->addOption(
                'host',
                'H',
                InputOption::VALUE_OPTIONAL,
                'The host or IP of your server (default to 127.0.0.1)',
                '127.0.0.1'
            )
            ->addOption(
                'port',
                'p',
                InputOption::VALUE_OPTIONAL,
                'The port listened by your server (default to 8888)',
                8888
            )
            ->addOption(
                'document-root',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Your document root directory (default to web)',
                'web'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $host = sprintf('%s:%d', $input->getOption('host'), $input->getOption('port'));
        $documentRoot = $this->projectRoot . DIRECTORY_SEPARATOR . $input->getOption('document-root');
        $this->processBuilder
            ->setArguments([PHP_BINARY, '-S', $host, '-t', $documentRoot])
            ->setTimeout(null);

        $process = $this->processBuilder->getProcess();

        $output->writeln(sprintf('Server running on <info>http://%s</info>', $host));
        $output->writeln('');

        $process->run(function($type, $buffer) use ($output) {
            $output->write($buffer);
        });

        if (!$process->isSuccessful()) {
            $output->writeln('<error>Built-in server terminated unexpectedly</error>');
            $output->writeln('<error>Run the command again with -v option for more details</error>');
        }

        return $process->getExitCode();
    }
}

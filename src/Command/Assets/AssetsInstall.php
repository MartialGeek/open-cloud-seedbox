<?php

namespace Martial\OpenCloudSeedbox\Command\Assets;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class AssetsInstall extends Command
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var array
     */
    private $sourcePaths;

    /**
     * @var string
     */
    private $destinationPath;

    /**
     * @param Filesystem $fs
     * @param array $sourcePaths
     * @param $destinationPath
     */
    public function __construct(Filesystem $fs, array $sourcePaths, $destinationPath)
    {
        $this->fs = $fs;
        $this->sourcePaths = $sourcePaths;
        $this->destinationPath = $destinationPath;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('assets:install')
            ->setDescription('Creates the symlinks from the assets paths to the web directory.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->sourcePaths as $sourcePath) {
            $targetDir = $this->destinationPath . substr($sourcePath, strrpos($sourcePath, DIRECTORY_SEPARATOR));
            $relativePath = $this->fs->makePathRelative($sourcePath, $this->destinationPath);

            $output->writeln(sprintf(
                'Link <info>%s</info> to <info>%s</info>',
                $relativePath,
                $targetDir
            ));

            $this->fs->symlink($relativePath, $targetDir);
        }
    }
}

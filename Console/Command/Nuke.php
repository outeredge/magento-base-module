<?php

declare(strict_types=1);

namespace OuterEdge\Base\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State\CleanupFiles;
use Magento\Framework\App\Cache\Frontend\Pool;

class Nuke extends Command
{
    protected $cleanupFiles;

    protected $cacheFrontendPool;

    public function __construct(
        CleanupFiles $cleanupFiles,
        Pool $cacheFrontendPool,
        ?string $name = null
    ) {
        parent::__construct($name);
        $this->cleanupFiles = $cleanupFiles;
        $this->cacheFrontendPool = $cacheFrontendPool;
    }

    protected function configure(): void
    {
        $this->setName('outeredge:nuke');
        $this->setDescription('Deletes all cache, static content and generated files');

        parent::configure();
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $exitCode = 0;

        try {
            $this->cleanupFiles->clearCodeGeneratedClasses();
            $this->cleanupFiles->clearAllFiles();

            foreach ($this->cacheFrontendPool as $cacheFrontend) {
                $cacheFrontend->getBackend()->clean();
            }

            $output->writeln('<comment>☢️ Successfully nuked all Magento cache, static content and generated files ☢️</comment>');
        } catch (LocalizedException $e) {
            $output->writeln(sprintf(
                '<error>%s</error>',
                $e->getMessage()
            ));
            $exitCode = 1;
        }

        return $exitCode;
    }
}

<?php

declare(strict_types=1);

namespace OuterEdge\Base\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\ResourceConnection;

class Nuke extends Command
{
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
        if (shell_exec('rm -rf generated pub/static/* var/view_preprocessed && bin/magento c:f')) {
            $output->writeln('<comment>☢️ Successfully nuked all Magento cache, static content and generated files ☢️</comment>');
            return 0;
        }

        return 1;
    }
}

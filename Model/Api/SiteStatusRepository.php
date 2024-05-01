<?php

namespace OuterEdge\Base\Model\Api;

use Exception;
use Magento\Indexer\Console\Command\IndexerStatusCommand;
use OuterEdge\Base\Api\SiteStatusRepositoryInterface;
use OuterEdge\Base\Console\Command\ConfigChanged;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;


class SiteStatusRepository implements SiteStatusRepositoryInterface
{
    private $consoleOutputIndexer;

    private $consoleOutputConfig;

    private $consoleInputIndexer;

    private $consoleInputConfig;

    public function __construct(
        protected IndexerStatusCommand $indexerStatusCommand,
        protected ConfigChanged $configChanged,
        BufferedOutput $consoleOutput = null,
        ArrayInput $consoleInput = null
    ) {
        $this->consoleInputConfig = $this->consoleInputIndexer = $consoleInput ? $consoleInput : new ArrayInput([]);
        $this->consoleOutputConfig = $consoleOutput ? $consoleOutput : new BufferedOutput();
        $this->consoleOutputIndexer = $consoleOutput ? $consoleOutput : new BufferedOutput();
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        try {
            //get indexer status
            $exitCode = $this->indexerStatusCommand->run($this->consoleInputIndexer, $this->consoleOutputIndexer);

            if ($exitCode) {
                throw new \RuntimeException(
                    sprintf('Command "%s" failed', 'indexer:status')
                );
            }

            //get config changed status
            $exitCode = $this->configChanged->run($this->consoleInputConfig, $this->consoleOutputConfig);

            if ($exitCode) {
                throw new \RuntimeException(
                    sprintf('Command "%s" failed', 'outeredge:config')
                );
            }

            $return = [
                'indexer' => $this->parseConsoleOutput($this->consoleOutputIndexer->fetch()),
                'configs' => $this->parseConsoleOutput($this->consoleOutputConfig->fetch())
            ];

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return ['success' => true, 'message' => $return];
    }

    private function parseConsoleOutput($output)
    {
        $rawLines = explode(PHP_EOL, $output);
        $data = $headers = [];
        $i = 0;

        foreach ($rawLines as $rowKey => $rawLine) {
            if (str_contains($rawLine, '+-')) {
                continue;
            }
            if ($rowKey == 1) {
                $headers = explode('|', $rawLine);
                continue;
            }

            $parts = explode('|', $rawLine);
            foreach ($parts as $key => $part) {
                if (!empty($part)) {
                    $data[$i][strtolower(trim($headers[$key]))] = $part;
                }
            }
            $i++;
        }

        return $data;
    }
}

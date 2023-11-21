<?php

namespace OuterEdge\Base\Model\Api;

use OuterEdge\Base\Api\SiteStatusRepositoryInterface;
use Magento\Indexer\Console\Command\IndexerStatusCommand;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Input\ArrayInput;
use OuterEdge\Base\Console\Command\ConfigChanged;

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
                'indexer' => $this->parseOutput($this->consoleOutputIndexer->fetch()),
                'configs' => $this->parseOutput($this->consoleOutputConfig->fetch())
            ];

        } catch (\Exception $e) {
            return json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        return json_encode(['success' => true, 'message' => $return]);
    }

    private function parseOutput($output)
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

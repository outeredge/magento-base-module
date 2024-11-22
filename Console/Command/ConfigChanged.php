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

class ConfigChanged extends Command
{
    private const HOURS = 'hours';
    private const LINES = 'lines';

    protected $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection,
        string $name = null
    ) {
        parent::__construct($name);
        $this->resourceConnection = $resourceConnection;
    }

    protected function configure(): void
    {
        $this->setName('outeredge:config');
        $this->setDescription('Lists recent core_config_data changes');

        $this->addOption(
            self::HOURS,
            null,
            InputOption::VALUE_REQUIRED,
            'Hours since last changed',
            24
        );

        $this->addOption(
            self::LINES,
            null,
            InputOption::VALUE_REQUIRED,
            'Number of lines to show',
            100
        );

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
        $existInEnvFile = false;
        $differentFromEnvFile = false;

        $hours = $input->getOption(self::HOURS);
        $lines = $input->getOption(self::LINES);

        try {
            //Read core config data from db
            $table = $this->resourceConnection->getTableName('core_config_data');
            $connection = $this->resourceConnection->getConnection();
            $query = "SELECT * FROM $table WHERE updated_at > now() - interval $hours hour ORDER BY updated_at DESC LIMIT $lines";
            $dbConfigData = $connection->fetchAll($query);

            $config = [];

            $fileConfig = __DIR__ . '/../../../../../app/etc/config.php';
            if (file_exists($fileConfig)) {
                $fileConfigDataArray = include($fileConfig);
                $fileConfigData = isset($fileConfigDataArray['system']['default']) ? $this->flatten($fileConfigDataArray['system']['default']) : [];
                $config = $fileConfigData;
            }

            $fileEnv = __DIR__ . '/../../../../../app/etc/env.php';
            if (file_exists($fileEnv)) {
                $fileEnvDataArray = include($fileEnv);
                $fileEnvgData = isset($fileEnvDataArray['system']['default']) ? $this->flatten($fileEnvDataArray['system']['default']) : [];
                $config = array_merge($config, $fileEnvgData);
            }

            $table = new Table($output);
            $table->setHeaders(['Path', 'Value (Database)', 'Value (config/env.php)'])
                  ->setColumnMaxWidth(0, 50)
                  ->setColumnMaxWidth(1, 50)
                  ->setColumnMaxWidth(2, 50);

            foreach ($dbConfigData as $dbConfig) {
                $dbPath = $dbConfig['path'];

                $fileConfigSelectedValue = null;
                $differentFromEnvFile    = false;
                $existInEnvFile          = false;

                if (array_key_exists($dbPath, $config)) {
                    $existInEnvFile           = true;
                    $fileConfigSelectedValue  = $config[$dbPath];
                    $dbConfigValue = $dbConfig['value'];

                    if ($fileConfigSelectedValue != $dbConfigValue) {
                        $differentFromEnvFile = true;
                    }
                }

                if ($differentFromEnvFile) {
                    $dbPath = '<comment>' . $dbPath . '</comment>';
                } elseif ($existInEnvFile) {
                    $dbPath = '<info>' . $dbPath . '</info>';
                }

                $table->addRow([
                    $dbPath,
                    ($dbConfig['value'] ?? 'null'),
                    ($fileConfigSelectedValue ?? 'null')
                ]);
            }

            $table->render();
        } catch (LocalizedException $e) {
            $output->writeln(sprintf(
                '<error>%s</error>',
                $e->getMessage()
            ));
            $exitCode = 1;
        }

        return $exitCode;
    }

    protected function flatten(array $array, $prefix = '') {
        $result = [];
        foreach($array as $key=>$value) {
            if(is_array($value)) {
                $result = $result + $this->flatten($value, $prefix . $key . '/');
            } else {
                $result[$prefix . $key] = $value;
            }
        }
        return $result;
    }
}

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
use Magento\Framework\App\DeploymentConfig\Writer;

class ConfigChanged extends Command
{
    private const HOURS = 'hours';
    private const LINES = 'lines';
    private const SAVE  = 'save';
    private const FORCE = 'force';

    protected $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection,
        protected Writer $writer,
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

        $this->addOption(
            self::SAVE,
            null,
            InputOption::VALUE_REQUIRED,
            'Save any values not currently in config.php',
            false
        );

        $this->addOption(
            self::FORCE,
            null,
            InputOption::VALUE_REQUIRED,
            'Override any existing values in config.php',
            false
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
        $existInEnvFile = $differentFromEnvFile = $configDataToOverwrite = false;
        $saveToConfigFile = [];

        $hours = $input->getOption(self::HOURS);
        $lines = $input->getOption(self::LINES);
        $save  = $input->getOption(self::SAVE);
        $force = $input->getOption(self::FORCE);

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
                    if ($force) {
                        $arrayWithValue = $this->convertToArray($dbPath, $dbConfig);
                        $saveToConfigFile = array_merge_recursive($saveToConfigFile, $arrayWithValue);
                    }

                    $dbPath = '<comment>' . $dbPath . '</comment>';
                } elseif ($existInEnvFile) {
                    $dbPath = '<info>' . $dbPath . '</info>';
                } elseif ($save) {
                    $arrayWithValue = $this->convertToArray($dbPath, $dbConfig);
                    $saveToConfigFile = array_merge_recursive($saveToConfigFile, $arrayWithValue);
                }

                $table->addRow([
                    $dbPath,
                    ($dbConfig['value'] ?? 'null'),
                    ($fileConfigSelectedValue ?? 'null')
                ]);
            }

            if ($save || $force) {
                $this->saveToConfig($output, $saveToConfigFile);
                $output->writeln("Save changes to config.php");
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

    private function convertToArray($dbPath, $dbConfig) {
        $arrayToConvert[$dbPath] = $dbConfig['value'];

        $result = array();
        foreach($arrayToConvert as $path => $value) {
            $temp =& $result;
            foreach(explode('/', $path) as $key) {
                $temp =& $temp[$key];
            }
            $temp = $value;
        }
        return $result;
    }

    private function saveToConfig($output, $saveToConfigFile) {
        $dump['system'] = ['default' => $saveToConfigFile];
        $this->writer->saveConfig(['app_config' => $dump], false);
    }
}

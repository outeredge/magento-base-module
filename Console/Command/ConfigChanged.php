<?php

declare(strict_types=1);

namespace OuterEdge\Base\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
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
        $this->setName('config:changed:command');
        $this->setDescription('List recent core_config_data changes');
        $this->addOption(
            self::HOURS,
            null,
            InputOption::VALUE_REQUIRED,
            'Hours'
        );
        $this->addOption(
            self::LINES,
            null,
            InputOption::VALUE_REQUIRED,
            'Lines'
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

        if ($hours = $input->getOption(self::HOURS)) {
            $output->writeln('<info>Provided hours is `' . $hours . '`</info>');
        } else {
            $hours = 240;
            $output->writeln('<info>Default hours is `' . $hours . '`</info>');
        }

        if ($lines = $input->getOption(self::LINES)) {
            $output->writeln('<info>Provided lines is `' . $lines . '`</info>');
        } else {
            $lines = 25;
            $output->writeln('<info>Default lines is `' . $lines . '`</info>');
        }

        try {
            //Read core config data from db
            $connection = $this->resourceConnection->getConnection();
            $table = $connection->getTableName('core_config_data');

            $query = "SELECT * FROM $table WHERE updated_at > now() - interval $hours hour ORDER BY updated_at DESC LIMIT $lines";
            $dbConfigData = $connection->fetchAll($query);

            $fileConfig = __DIR__ . '/../../../../../app/etc/config.php';
            $fileConfigDataArray = include($fileConfig);
            $fileConfigData = $this->flatten($fileConfigDataArray['system']['default']);

            $fileEnv = __DIR__ . '/../../../../../app/etc/env.php';
            $fileEnvDataArray = include($fileEnv);
            $fileEnvgData = $this->flatten($fileEnvDataArray['system']['default']);

            $fileConfigsData = array_merge($fileConfigData, $fileEnvgData);

            $output->writeln('<info>Recent core_config_data changes.</info>');
            foreach ($dbConfigData as $dbConfig) {

                $dbPatch = $dbConfig['path'];

                if (array_key_exists($dbPatch, $fileConfigsData)) {
                    $existInEnvFile = true;
                    $fileConfigSelectedValue = $fileConfigsData[$dbPatch];
                    $dbConfigValue = $dbConfig['value'];

                    if ($fileConfigSelectedValue != $dbConfigValue) {
                        $differentFromEnvFile = true;
                    } else {
                        $differentFromEnvFile = false;
                    }
                } else {
                    $existInEnvFile = false;
                }

                if ($existInEnvFile && $differentFromEnvFile) {
                    $output->writeln(sprintf(
                        '<error>File need update: %s :: %s </error>',
                        $dbPatch,
                        $dbConfig['value']
                    ));
                }

                $output->writeln('<info>'.$dbPatch . ' :: ' . $dbConfig['value'].'</info>');
            }

            $output->writeln('<info>Completed.</info>');
        } catch (LocalizedException $e) {
            $output->writeln(sprintf(
                '<error>%s</error>',
                $e->getMessage()
            ));
            $exitCode = 1;
        }

        return $exitCode;
    }

    private function flatten($array, $prefix = '') {
        $result = array();
        foreach($array as $key=>$value) {
            if(is_array($value)) {
                $result = $result + $this->flatten($value, $prefix . $key . '/');
            }
            else {
                $result[$prefix . $key] = $value;
            }
        }
        return $result;
    }
}

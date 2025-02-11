<?php

declare(strict_types=1);

namespace OuterEdge\Base\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\ResourceConnection;

class EavAttributeCheck extends Command
{
    protected $columnsToCheck = ['attribute_model', 'backend_model', 'frontend_model', 'source_model'];

    public function __construct(
        protected ResourceConnection $resourceConnection,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('outeredge:eav_check');
        $this->setDescription('Prevent errors when eav attribute models dont exist');

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
            $table = $this->resourceConnection->getTableName('eav_attribute');
            $connection = $this->resourceConnection->getConnection();
            $query = "SELECT * FROM $table";

            foreach ($connection->fetchAll($query) as $data) {
                $attributeId = $data['attribute_id'];
                foreach ($this->columnsToCheck as $column) {
                    $class = $data[$column];
                    if (!is_null($class)) {
                        if (!class_exists($class)) {
                            $connection->query("UPDATE $table SET $column = NULL WHERE attribute_id = $attributeId");
                            $output->writeln(sprintf('<comment>☢️ %s not exist, will be removed from attribute %s ☢️</comment>', $class, $attributeId));
                        }
                    }
                }
            }
            $output->writeln('<comment>☢️ Eav table is clean for not existing class ☢️</comment>');
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

<?php

declare(strict_types=1);

namespace OuterEdge\Base\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\ResourceConnection;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class EavAttributeCheck extends Command
{
    protected $columnsToCheck = ['attribute_model', 'backend_model', 'frontend_model', 'source_model'];

    public function __construct(
        protected ResourceConnection $resourceConnection,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('outeredge:eav-clean');
        $this->setDescription('Prevent errors when eav attribute models no longer exist');

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

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Are you sure you want to continue? ', false);

        try {
            $deleteArray = [];
            $table = $this->resourceConnection->getTableName('eav_attribute');
            $connection = $this->resourceConnection->getConnection();
            $query = "SELECT * FROM $table";

            foreach ($connection->fetchAll($query) as $data) {
                $attributeId = $data['attribute_id'];
                $attributeCode = $data['attribute_code'];
                foreach ($this->columnsToCheck as $column) {
                    $class = $data[$column];
                    if (!is_null($class)) {
                        if (!class_exists($class)) {
                            $output->writeln(sprintf("<comment>❌ The type '%s' doesn't exist and will be removed from attribute '%s'</comment>", $class, $attributeCode));
                            $deleteArray[] = compact('column', 'attributeId');
                        }
                    }
                }
            }

            if (!empty($deleteArray) && $helper->ask($input, $output, $question)) {
                foreach($deleteArray as $row) {
                    $column = $row['column'];
                    $attributeId = $row['attributeId'];
                    $connection->query("UPDATE $table SET $column = NULL WHERE attribute_id = $attributeId");
                }
                $output->writeln('<comment>EAV table cleanup complete! ✅</comment>');
            } else {
                $output->writeln("<comment>No changes required!</comment>");
            }
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

<?php

namespace OuterEdge\Base\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        $connection->addColumn('cms_page','banner_image',
            [
                'type' =>\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'comment' => 'Banner Image'
            ]);
        $installer->endSetup();
    }
}

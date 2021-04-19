<?php

namespace OuterEdge\Base\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '0.0.2', '<')) {
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
}

<?php
namespace Convert\OldOrder\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

	public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
	{
		$installer = $setup;
		$installer->startSetup();
		if (!$installer->tableExists('old_order')) {
			$table = $installer->getConnection()->newTable(
				$installer->getTable('old_order')
			)
				->addColumn(
					'id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[
						'identity' => true,
						'nullable' => false,
						'primary'  => true,
						'unsigned' => true,
					],
					'ID'
				)
				->addColumn(
					'store_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					['nullable' => false],
					'Store ID'
				)
				->addColumn(
					'firstname',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					['nullable' => false],
					'First name'
				)
				->addColumn(
					'lastname',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					['nullable' => false],
					'Last name'
                )
                
				->addColumn(
					'email_address',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					['nullable' => false],
					'Email address'
				)
				->addColumn(
					'order_increment_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					['nullable' => false],
					'Order increment id'
                )
				// ->addColumn(
				// 	'subtotal',
				// 	\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				// 	255,
				// 	['nullable' => false,'default' => '0'],
				// 	'Sub total'
				// )
				->addColumn(
					'tax',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					['nullable' => false,'default' => '0'],
					'Tax'
				)
				->addColumn(
					'shipping',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					['nullable' => false,'default' => '0'],
					'Shipping'
				)
				->addColumn(
					'shipping',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					['nullable' => false,'default' => '0'],
					'Shipping'
				)
				->addColumn(
					'discount',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					['nullable' => false,'default' => '0'],
					'discount'
				)
				->addColumn(
					'grand_total',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					['nullable' => false,'default' => '0'],
					'grand_total'
				)
				
    //             ->addColumn(
				// 	'status',
				// 	\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
				// 	null,
				// 	['nullable' => false,'default' => '0'],
				// 	'Status'
				// )
				->addColumn(
					'payment_method_name',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					['nullable' => false,'default' => '0'],
					'payment_method_name'
				)
				->addColumn(
					'shipping_method_name',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					['nullable' => false,'default' => '0'],
					'shipping_method_name'
				)
				->addColumn(
					'ordered_date',
					\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					null,
					['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
					'Ordered date'
				)
				->setComment('Old Order Table');
			$installer->getConnection()->createTable($table);
			$installer->getConnection()->addIndex(
				$installer->getTable('old_order'),
				$setup->getIdxName(
					$installer->getTable('old_order'),
					['firstname', 'lastname', 'shipping_method_firstname'],
					\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
				),
				['firstname', 'lastname', 'shipping_method_name'],
				\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
			);
			
		}
		$installer->endSetup();
	}
}
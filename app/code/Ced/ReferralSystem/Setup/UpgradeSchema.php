<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_ReferralSystem
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\ReferralSystem\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
    
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('ced_discount_coupons'),
                'amount',
                [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        'nullable' => true,
                        'length' => '12,4',
                        'default' => '0.0000',
                        'comment' => 'Discount Amount'
                ]
            );
        }
        
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('ced_discount_coupons'),
                'cart_amount',
                [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        'nullable' => true,
                        'length' => '12,4',
                        'default' => '0.0000',
                        'comment' => 'Cart Amount'
                ]
            );
        }
        
        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('ced_referral_transaction'),
                'transaction_type',
                [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        'length' => null, 
                        'nullable' => false, 
                        'comment' => 'Transaction Type'
                ]
            );
        }

        /* Table For Traffic Source */
        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $table = $setup->getConnection()->newTable(
            $setup->getTable('ced_referral_source')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Customer ID'
            )->addColumn(
                'referred_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Referral Email'
            )->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
            )->addColumn(
                'source',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Referral Source'
            )->setComment(
                'Referral Source Table'
            );
            $setup->getConnection()->createTable($table);
            $setup->endSetup();
        }
        
        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('ced_discount_coupons'),
                'email_id',
                [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => false,
                        'length' => null,
                        'default' => '',
                        'comment' => 'Email Id'
                ]
            );
        }
    }
}
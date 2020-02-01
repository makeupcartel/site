<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Checkoutspage v2.x.x
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Checkoutspage\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        try {
            $setup->startSetup();
            $tableName = $setup->getTable('sales_order');

            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $connection = $setup->getConnection();
                 $connection->addColumn(
                    $tableName,
                    'next_order_promo_code',
                    ['type' => Table::TYPE_TEXT,'nullable' => false, 'default' => '', 'length' => 32, 'comment' => 'Next Order Promo Code']
                );
            }

            $setup->endSetup();
        } catch (\Exception $e) {}

    }
}
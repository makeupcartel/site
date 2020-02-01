<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Setup\Operation;

use Amasty\Reports\Api\Data\RuleInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class UpdateCustomReports
 */
class UpdateCustomReports
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(RuleInterface::TABLE_NAME),
            RuleInterface::PIN,
            [
                'type' => Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default' => false,
                'comment' => 'Pin in Custom Reports'
            ]
        );
    }
}

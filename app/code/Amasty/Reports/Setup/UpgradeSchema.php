<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class UpgradeSchema
 * @package Amasty\Reports\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Operation\RuleTable
     */
    private $ruleTable;

    /**
     * @var Operation\RuleIndexTable
     */
    private $ruleIndexTable;

    /**
     * @var Operation\UpdateBrandAttribute
     */
    private $updateBrandAttribute;

    /**
     * @var Operation\AbandonedCart
     */
    private $abandonedCart;
    /**
     * @var Operation\UpdateCustomReports
     */
    private $updateCustomReports;

    public function __construct(
        Operation\RuleTable $ruleTable,
        Operation\RuleIndexTable $ruleIndexTable,
        Operation\UpdateBrandAttribute $updateBrandAttribute,
        Operation\AbandonedCart $abandonedCart,
        Operation\UpdateCustomReports $updateCustomReports
    ) {
        $this->ruleTable = $ruleTable;
        $this->ruleIndexTable = $ruleIndexTable;
        $this->updateBrandAttribute = $updateBrandAttribute;
        $this->abandonedCart = $abandonedCart;
        $this->updateCustomReports = $updateCustomReports;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->ruleTable->execute($setup);
            $this->ruleIndexTable->execute($setup);
        }
        if (version_compare($context->getVersion(), '1.2.0', '<')) {
            $this->updateBrandAttribute->execute($setup);
        }
        if (version_compare($context->getVersion(), '1.4.0', '<')) {
            $this->abandonedCart->execute($setup);
        }
        if (version_compare($context->getVersion(), '1.5.0', '<')) {
            $this->updateCustomReports->execute($setup);
        }

        $setup->endSetup();
    }
}

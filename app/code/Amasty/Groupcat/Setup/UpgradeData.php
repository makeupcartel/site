<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Upgrade Data script
 */
class UpgradeData implements UpgradeDataInterface
{
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.2.0', '<')) {
            $migrationInstaller = $setup->createMigrationSetup();
            $migrationInstaller->appendClassAliasReplace(
                'amasty_groupcat_rule',
                'conditions_serialized',
                \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
                \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_SERIALIZED,
                ['rule_id']
            );
            $migrationInstaller->doUpdateClassAliases();
        }
        if ($context->getVersion() && version_compare($context->getVersion(), '1.2.0', '<')) {
            $this->copyOldData($setup);
        }

        $setup->endSetup();
    }

    /**
     * Move data of amasty_amgroupcat_rules_old table to amasty_groupcat_rule table
     *
     * @param ModuleDataSetupInterface $setup
     */
    private function copyOldData(ModuleDataSetupInterface $setup)
    {
        if (!$setup->tableExists($setup->getTable('amasty_amgroupcat_rules_old'))) {
            return;
        }
        $connection = $setup->getConnection();
        /** @codingStandardsIgnoreStart */
        /* this data will be prepared, parsed and inserted to new tables */
        $relationsSelect = $connection->select()->from(
            $setup->getTable('amasty_amgroupcat_rules_old'),
            [
                'rule_id',
                'forbidden_cms_page',
                'price_on_product_view',
                'price_on_product_list',
                'customer_group_ids',
                'stores',
                'categories',
                'matched_prod_ids'
            ]
        );
        $ruleRelationsDataSet = $connection->fetchAll($relationsSelect);

        $ruleDataSelect = $connection->select()->from(
            $setup->getTable('amasty_amgroupcat_rules_old'),
            [
                'rule_id',
                'name',
                'enabled',
                'prod_cond_serialize',
                'forbidden_action',
                'allow_direct_links',
                'remove_product_links',
                'remove_category_links',
                'hide_price',
                'stock_status',
                'from_date',
                'to_date',
                'date_range_enabled',
                'from_price',
                'to_price',
                'by_price',
                'price_range_enabled',
                'customer_group_enabled'
            ]
        );
        $insertSql = $connection->insertFromSelect(
            $ruleDataSelect,
            $setup->getTable('amasty_groupcat_rule'),
            [
                'rule_id',
                'name',
                'is_active',
                'conditions_serialized',
                'forbidden_action',
                'allow_direct_links',
                'hide_product',
                'hide_category',
                'price_action',
                'stock_status',
                'from_date',
                'to_date',
                'date_range_enabled',
                'from_price',
                'to_price',
                'by_price',
                'price_range_enabled',
                'customer_group_enabled'
            ]
        );
        $connection->query($insertSql);
        /** @codingStandardsIgnoreEnd */

        /* insert relations from old table to new */
        foreach ($ruleRelationsDataSet as $ruleRow) {
            $this->convertForbiddenCmsPage($setup, $ruleRow);
            $this->prepareBlockIdsForForeign($setup, $ruleRow);
            $this->copyOldCustomerGroupToNew($setup, $ruleRow);
            $this->copyOldStoresToNew($setup, $ruleRow);
            $this->copyOldCategoriesToNew($setup, $ruleRow);
        }
    }

    /**
     * Prepare CMS Block ID for foreign key.
     *
     * @since 1.2 Column names changed from price_on_product_view to block_id_view
     *        and from price_on_product_list to block_id_list
     *
     * @param ModuleDataSetupInterface $setup
     * @param array                    $ruleRow
     */
    private function prepareBlockIdsForForeign(ModuleDataSetupInterface $setup, $ruleRow)
    {
        $blockIds = [];
        if ($ruleRow['price_on_product_view']) {
            $blockIds[] = (int)$ruleRow['price_on_product_view'];
        }
        if ($ruleRow['price_on_product_list']) {
            $blockIds[] = (int)$ruleRow['price_on_product_list'];
        }
        if (!count($blockIds)) {
            return;
        }
        $connection = $setup->getConnection();
        /** @codingStandardsIgnoreStart */
        $blockSelect = $connection->select()
            ->from($setup->getTable('cms_block'), ['block_id'])
            ->where('block_id IN (?)', $blockIds);

        $blockSet = $connection->fetchAll($blockSelect);
        /** @codingStandardsIgnoreEnd */
        $updateBind = [];

        foreach ($blockSet as $blockRow) {
            if ($blockRow['block_id'] == $ruleRow['price_on_product_view']) {
                $updateBind['block_id_view'] =  $blockRow['block_id'];
            }
            if ($blockRow['block_id'] == $ruleRow['price_on_product_list']) {
                $updateBind['block_id_list'] = $blockRow['block_id'];
            }
        }

        if (count($updateBind) && $ruleRow['rule_id']) {
            $connection->update(
                $setup->getTable('amasty_groupcat_rule'),
                $updateBind,
                $connection->quoteInto('rule_id = ?', $ruleRow['rule_id'])
            );
        }
    }

    /**
     * convert CMS Page identifier to CMS Page ID for foreign key.
     *
     * @param ModuleDataSetupInterface $setup
     * @param array                    $ruleRow
     */
    private function convertForbiddenCmsPage(ModuleDataSetupInterface $setup, $ruleRow)
    {
        if (!$ruleRow['forbidden_cms_page']) {
            return;
        }
        $connection = $setup->getConnection();
        /** @codingStandardsIgnoreStart */
        $pageSelect = $connection->select()
            ->from($setup->getTable('cms_page'), ['page_id'])
            ->where('identifier = ?', $ruleRow['forbidden_cms_page']);
        $pageId = $connection->fetchOne($pageSelect);
        /** @codingStandardsIgnoreEnd */
        if ($pageId && $ruleRow['rule_id']) {
            $connection->update(
                $setup->getTable('amasty_groupcat_rule'),
                ['forbidden_page_id' => $pageId],
                $connection->quoteInto('rule_id = ?', $ruleRow['rule_id'])
            );
        }
    }

    /**
     * Copy data from old table
     *
     * @param ModuleDataSetupInterface $setup
     * @param array                    $ruleRow
     */
    private function copyOldCustomerGroupToNew(ModuleDataSetupInterface $setup, $ruleRow)
    {
        if (!is_string($ruleRow['customer_group_ids']) || empty($ruleRow['customer_group_ids'])) {
            return;
        }
        $this->prepareAndInsertOldData(
            $setup,
            $ruleRow,
            /** @codingStandardsIgnoreStart */
            @unserialize($ruleRow['customer_group_ids']),
            /** @codingStandardsIgnoreEnd */
            $setup->getTable('customer_group'),
            $setup->getTable('amasty_groupcat_rule_customer_group'),
            'customer_group_id',
            'customer_group_id'
        );
    }

    /**
     * Copy data from old table
     *
     * @param ModuleDataSetupInterface $setup
     * @param array                    $ruleRow
     */
    private function copyOldStoresToNew(ModuleDataSetupInterface $setup, $ruleRow)
    {
        $this->prepareAndInsertOldData(
            $setup,
            $ruleRow,
            $ruleRow['stores'],
            $setup->getTable('store'),
            $setup->getTable('amasty_groupcat_rule_store'),
            'store_id',
            'store_id'
        );
    }

    /**
     * Copy data from old table
     *
     * @param ModuleDataSetupInterface $setup
     * @param array                    $ruleRow
     */
    private function copyOldCategoriesToNew(ModuleDataSetupInterface $setup, $ruleRow)
    {
        $this->prepareAndInsertOldData(
            $setup,
            $ruleRow,
            $ruleRow['categories'],
            $setup->getTable('catalog_category_entity'),
            $setup->getTable('amasty_groupcat_rule_category'),
            'category_id'
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param array                    $ruleRow
     * @param string|array             $idsSet       ids separated by commas
     * @param string                   $checkTableName  Fro avoid Foreign key error
     * @param string                   $insertTableName Table should have only 2 columns: 'rule_id' and $targetIdName
     * @param string                   $targetIdName
     * @param string                   $entityIdName    ID name of $checkTableName
     */
    private function prepareAndInsertOldData(
        ModuleDataSetupInterface $setup,
        $ruleRow,
        $idsSet,
        $checkTableName,
        $insertTableName,
        $targetIdName,
        $entityIdName = 'entity_id'
    ) {
        $insertData = [];
        /* old Relation data was stored in one cell separated by commas */
        $idsArray = is_string($idsSet) ? explode(',', trim($idsSet, ',')) : $idsSet;
        if (!is_array($idsArray) || count($idsArray) < 1) {
            return;
        }
        /* get only exist ids. Avoid Foreign key error */
        /** @codingStandardsIgnoreStart */
        $select = $setup->getConnection()->select()
            ->from($checkTableName, [$entityIdName])
            ->where($entityIdName . ' IN (?)', $idsArray);
        $idsArray = $setup->getConnection()->fetchCol($select);
        /** @codingStandardsIgnoreEnd */

        foreach ($idsArray as $entityId) {
            $insertData[] = [$ruleRow['rule_id'], $entityId];
        }
        if (count($insertData)) {
            $setup->getConnection()->insertArray($insertTableName, ['rule_id', $targetIdName], $insertData);
        }
    }
}

<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Traits;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

trait Filters
{
    /**
     * @param $collection
     * @param string $tablePrefix
     */
    public function addFromFilter($collection, $tablePrefix = 'main_table')
    {
        $filters = $this->getRequestParams();
        $from = $filters['from'] ?? date('Y-m-d', $this->helper->getDefaultFromDate());

        if ($from) {
            $from = $this->helper->getDateForLocale($from);
            $collection->getSelect()->where($tablePrefix . '.created_at >= ?', $from);
        }
    }

    /**
     * @param $collection
     * @param string $tablePrefix
     */
    public function addToFilter($collection, $tablePrefix = 'main_table')
    {
        $filters = $this->getRequestParams();
        $to = $filters['to'] ?? date('Y-m-d', $this->helper->getDefaultToDate());

        if ($to) {
            $to = $this->helper->getDateForLocale($to, 23, 59, 59);
            $collection->getSelect()->where($tablePrefix . '.created_at <= ?', $to);
        }
    }

    /**
     * @param $collection
     * @param string $tablePrefix
     */
    public function addStoreFilter($collection, $tablePrefix = 'main_table')
    {
        $filters = $this->getRequestParams();
        $store = isset($filters['store']) && $filters['store'] ? $filters['store'] : false;

        if ($store) {
            $collection->getSelect()->where($tablePrefix . '.store_id = ?', $store);
        }
    }

    /**
     * @return array
     */
    private function getRequestParams()
    {
        $params = $this->request->getParams();
        $params = array_merge($params, $this->request->getParam('filters') ?: []);
        $params = array_merge($params, $this->request->getParam('amreports') ?: []);

        return $params;
    }

    public function addInterval($collection, $dateFiled = 'created_at', $tablePrefix = 'main_table')
    {
        $filters = $this->request->getParam('amreports');
        $interval = isset($filters['interval']) ? $filters['interval'] : 'day';
        $collection->setFlag('interval', $interval);
        switch ($interval) {
            case 'year':
                $collection->getSelect()
                    ->columns([
                        'period' => "YEAR($dateFiled)",
                    ])
                    ->group("YEAR($tablePrefix.$dateFiled)");
                break;
            case 'month':
                $collection->getSelect()
                    ->columns([
                        'period' => "DATE_FORMAT($tablePrefix.$dateFiled,'%Y-%m')",
                    ])
                    ->group("MONTH($tablePrefix.$dateFiled)");
                break;
            case 'week':
                $collection->getSelect()
                    ->columns([
                        'period' => "CONCAT(ADDDATE(DATE($tablePrefix.$dateFiled), "
                            . "INTERVAL 1-DAYOFWEEK($tablePrefix.$dateFiled) DAY), "
                            . "' - ', ADDDATE(DATE($tablePrefix.$dateFiled), "
                            . "INTERVAL 7-DAYOFWEEK($tablePrefix.$dateFiled) DAY))",
                    ])
                    ->group("WEEK($tablePrefix.$dateFiled)");
                break;
            case 'day':
            default:
                $collection->getSelect()
                    ->columns([
                        'period' => "DATE($tablePrefix.$dateFiled)",
                    ])
                    ->group('DATE(' . $tablePrefix . '.' . $dateFiled . ')');
        }
    }

    public function createUniqueEntity()
    {
        $filters = $this->request->getParam('amreports');
        $from = isset($filters['from']) ? $filters['from'] : date('Y-m-d', $this->helper->getDefaultFromDate());
        $to = isset($filters['to']) ? $filters['to'] : false;
        $store = isset($filters['store']) ? $filters['store'] : false;
        $interval = isset($filters['interval']) ? $filters['interval'] : 'day';
        $group = isset($filters['type']) ? $filters['type'] : 'overview';

        return sha1($from . $to . $store . $interval . $group);
    }

    /**
     * @param $collection
     */
    public function addStatusFilter($collection)
    {
        $statuses = $this->helper->getStatuses();
        if (!empty($statuses)) {
            $fromPart = $collection->getSelect()->getPart(Select::FROM);
            $orderTable = $collection->getTable('sales_order');
            $statusTable = 'main_table';
            $mainTableName = $fromPart['main_table']['tableName'];
            if (!isset($fromPart[$orderTable]) && $mainTableName != $orderTable && !isset($fromPart['sales_order'])) {
                $tableWithOrderId = $mainTableName == $collection->getTable('catalog_product_entity')
                    ? 'sales_order_item'
                    : 'main_table';
                $collection->getSelect()
                    ->joinLeft(
                        ['order_table' => $orderTable],
                        'order_table.entity_id = ' . $tableWithOrderId . '.order_id'
                    );
                $statusTable = 'order_table';
            }

            if (isset($fromPart['sales_order'])) {
                $statusTable = 'sales_order'; // product perfomance report
            }

            $collection->addFieldToFilter($statusTable . '.status', ['in' => $statuses]);
        }
    }

    /**
     * @param AbstractCollection $collection
     */
    public function addReportRules($collection)
    {
        $filters = $this->getRequestParams();
        if (isset($filters['rule']) && $filters['rule']) {
            $storeId = isset($filters['store']) ? (int)$filters['store'] : 0;
            $productIds = $this->ruleIndex->getAppliedProducts(
                (int)$filters['rule'],
                $storeId
            );
            $collection->getSelect()->where('sales_order_item.product_id in (?)', $productIds);
        }
    }

    /**
     * @param AbstractCollection $collection
     * @param string $attributeCode
     * @param string $tablePrefix
     */
    public function joinCustomAttribute($collection, $attributeCode, $tablePrefix = 'main_table')
    {
        $collection->getSelect()->joinLeft(
            ['ea1_' . $attributeCode => $this->getTable('eav_attribute')],
            sprintf('%s.attribute_code = \'%s\'', 'ea1_' . $attributeCode, $attributeCode),
            ['']
        )->joinLeft(
            ['cpei1_' . $attributeCode => $this->getIndexEavSelect($collection->getConnection())],
            sprintf(
                '%2$s.product_id = %1$s.' . $this->getIndexEavColumn()
                . ' AND %2$s.store_id = %1$s.store_id AND '
                . '%1$s.attribute_id = %3$s.attribute_id',
                'cpei1_' . $attributeCode,
                $tablePrefix,
                'ea1_' . $attributeCode
            ),
            ['']
        )->joinLeft(
            ['eaov1_' . $attributeCode => $this->getTable('eav_attribute_option_value')],
            sprintf(
                '%1$s.value = %2$s.option_id AND %2$s.store_id = 0',
                'cpei1_' . $attributeCode,
                'eaov1_' . $attributeCode
            ),
            ['']
        )->group(
            sprintf('IF (%1$s.value IS NOT NULL, %1$s.value, 0)', 'cpei1_' . $attributeCode)
        );
    }

    /**
     * return catalog_product_index_eav with unique row with attribute value for all items
     *
     * @return Select
     */
    private function getIndexEavSelect()
    {
        return $this->_conn->select()->from(
            $this->getTable('catalog_product_index_eav')
        )->group(
            'source_id'
        )->group(
            'attribute_id'
        )->group(
            'store_id'
        )->group(
            'value'
        );
    }

    /**
     * @return string
     */
    private function getIndexEavColumn()
    {
        $eavColumn = 'entity_id';
        $columns = array_keys($this->_conn->describeTable($this->getTable('catalog_product_index_eav')));
        if (in_array('source_id', $columns)) {
            $eavColumn = 'source_id';
        }

        return $eavColumn;
    }

    /**
     * @return array
     */
    public function getStatusesOrder()
    {
        return array_map(
            function ($value) {
                return trim($value);
            },
            array_filter(explode(',', $this->helper->getStatuses()))
        );
    }
}

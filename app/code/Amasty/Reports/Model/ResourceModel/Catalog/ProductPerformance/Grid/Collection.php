<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\ResourceModel\Catalog\ProductPerformance\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class Collection
 */
class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    const NOT_LOGGED = 0;

    private $mappedFields = [
        'order' => 'sales_order.increment_id',
        'customer_group_id' => 'customer_group.customer_group_id',
        'group' => 'customer_group.customer_group_id',
        'date' => 'sales_order.created_at',
        'name' => 'CONCAT(customer.firstname, " ", customer.lastname)',
        'qty' => 'main_table.qty_ordered',
        'revenue' => 'main_table.base_row_total'
    ];

    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        \Amasty\Reports\Model\ResourceModel\Catalog\ProductPerformance\Collection $filterApplier,
        $mainTable = 'sales_order_item',
        $resourceModel = \Amasty\Reports\Model\ResourceModel\Catalog\ProductPerformance\Collection::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);

        $filterApplier->prepareCollection($this);
    }

    protected function _construct()
    {
        foreach ($this->mappedFields as $field => $mappedField) {
            $this->addFilterToMap($field, new \Zend_Db_Expr($mappedField));
        }
        parent::_construct();
    }

    /**
     * @inheritDoc
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'customer_group_id' && isset($condition['eq']) && $condition['eq'] == self::NOT_LOGGED) {
            unset($condition['eq']);
            $condition['null'] = true;
        }
        return parent::addFieldToFilter($field, $condition);
    }
}

<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\ResourceModel\Customers\Conversion\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class Collection
 * @package Amasty\Reports\Model\ResourceModel\Customers\Conversion\Grid
 */
class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        \Amasty\Reports\Model\ResourceModel\Customers\Conversion\Collection $filterApplier,
        $mainTable = 'customer_visitor',
        $resourceModel = \Amasty\Reports\Model\ResourceModel\Customers\Conversion\Collection::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);

        $filterApplier->prepareCollection($this, 'main_table');
    }

    /**
     * @param string $condition
     * @param int $value
     * @return $this
     */
    public function addVisitorsFilter($condition, $value)
    {
        $condition = $this->_getConditionSql('COUNT(main_table.session_id)', [$condition => $value]);
        $this->getSelect()->having($condition);
        return $this;
    }

    /**
     * @param string $condition
     * @param int $value
     * @return $this
     */
    public function addPlacedOrdersFilter($condition, $value)
    {
        $condition = $this->_getConditionSql(
            'COUNT(DISTINCT orderTable.entity_id)',
            [$condition => $value]
        );
        $this->getSelect()->having($condition);
        return $this;
    }

    /**
     * @param string $condition
     * @param int $value
     * @return $this
     */
    public function addConversionFilter($condition, $value)
    {
        $condition = $this->_getConditionSql(
            'ROUND(COUNT(DISTINCT orderTable.entity_id) / COUNT(DISTINCT main_table.session_id) * 100)',
            [$condition => $value]
        );
        $this->getSelect()->having($condition);
        return $this;
    }
}

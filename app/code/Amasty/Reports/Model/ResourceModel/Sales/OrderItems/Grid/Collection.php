<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\ResourceModel\Sales\OrderItems\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class Collection
 */
class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @var array
     */
    private $map = [
        'product_sku' => 'main_table.sku',
        'product_name' => 'main_table.name',
        'order_date' => 'sales_order.created_at',
        'order_status' => 'sales_order.status'
    ];

    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        \Amasty\Reports\Model\ResourceModel\Sales\OrderItems\Collection $filterApplier,
        $mainTable = 'sales_order_item',
        $resourceModel = \Amasty\Reports\Model\ResourceModel\Sales\OrderItems\Collection::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);

        $filterApplier->prepareCollection($this);
    }

    protected function _construct()
    {
        parent::_construct();
        foreach ($this->map as $alias => $field) {
            $this->addFilterToMap(
                $alias,
                $field
            );
        }
    }
}

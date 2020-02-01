<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\ResourceModel\Catalog\Bestsellers\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class Collection
 * @package Amasty\Reports\Model\ResourceModel\Catalog\Bestsellers\Grid
 */
class Collection extends SearchResult
{

    /**
     * Collection constructor.
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param \Amasty\Reports\Model\ResourceModel\Catalog\Bestsellers\Collection $filterApplier
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        \Amasty\Reports\Model\ResourceModel\Catalog\Bestsellers\Collection $filterApplier,
        $mainTable = 'sales_order',
        $resourceModel = \Amasty\Reports\Model\ResourceModel\Catalog\Bestsellers\Collection::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);

        $filterApplier->prepareCollection($this);
    }
    
    protected function _construct()
    {
        parent::_construct();
        $this->addFilterToMap(
            'sku',
            'sales_order_item.sku'
        );
    }
}

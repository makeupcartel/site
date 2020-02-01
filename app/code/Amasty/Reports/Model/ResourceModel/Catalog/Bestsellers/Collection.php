<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\ResourceModel\Catalog\Bestsellers;

use Amasty\Reports\Helper\Data;
use Amasty\Reports\Model\ResourceModel\RuleIndex;
use Amasty\Reports\Traits\Filters;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Helper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Psr\Log\LoggerInterface;

/**
 * Class Collection
 * @package Amasty\Reports\Model\ResourceModel\Catalog\Bestsellers
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Order\Collection
{
    use Filters;

    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var RuleIndex
     */
    protected $ruleIndex;

    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Snapshot $entitySnapshot,
        Helper $coreResourceHelper,
        RequestInterface $request, // TODO move it out of here
        Data $helper,
        RuleIndex $ruleIndex,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $entitySnapshot,
            $coreResourceHelper,
            $connection,
            $resource
        );
        $this->request = $request;
        $this->helper = $helper;
        $this->ruleIndex = $ruleIndex;
    }

    /**
     * @param $collection
     * @return mixed
     */
    public function prepareCollection($collection)
    {
        $this->applyBaseFilters($collection);
        $this->applyToolbarFilters($collection);
        return $collection;
    }

    /**
     * @param $collection
     */
    public function joinCategoryTable($collection)
    {
        $collection->getSelect()
            ->join(
                ['sales_order_item' => $this->getTable('sales_order_item')],
                'sales_order_item.order_id = main_table.entity_id'
            )
            ->where('sales_order_item.parent_item_id IS NULL')
        ;
    }

    /**
     * @param $collection
     */
    public function applyBaseFilters($collection)
    {
        $this->joinCategoryTable($collection);
        $collection->getSelect()
            ->reset(\Zend_Db_Select::COLUMNS);
        $collection->getSelect()
            ->columns([
                'total' => 'SUM(sales_order_item.base_row_total)',
                'qty' => 'FLOOR(SUM(sales_order_item.qty_ordered))',
                'sku' => 'sales_order_item.sku',
                'name' => 'sales_order_item.name',
                'order_id' => 'CONCAT(sales_order_item.order_id,\''.$this->createUniqueEntity().'\')'
            ]);
    }

    /**
     * @param $collection
     */
    public function applyToolbarFilters($collection)
    {
        $this->addReportRules($collection);
        $this->addFromFilter($collection);
        $this->addToFilter($collection);
        $this->addStoreFilter($collection);
        $this->addGroupBy($collection);
    }

    /**
     * @param $collection
     */
    public function addGroupBy($collection)
    {
        $collection->getSelect()->group("sales_order_item.sku");
        $collection->getSelect()->order('FLOOR(SUM(sales_order_item.qty_ordered)) DESC');
    }
}

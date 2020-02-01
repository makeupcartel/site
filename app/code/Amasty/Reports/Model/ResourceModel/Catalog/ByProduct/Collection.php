<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\ResourceModel\Catalog\ByProduct;

use Amasty\Reports\Traits\Filters;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Collection
 * @package Amasty\Reports\Model\ResourceModel\Catalog\ByProduct
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Order\Collection
{
    use Filters;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    /**
     * @var \Amasty\Reports\Helper\Data
     */
    protected $helper;

    /**
     * @var \Amasty\Reports\Model\ResourceModel\RuleIndex
     */
    protected $ruleIndex;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\DB\Helper $coreResourceHelper,
        \Magento\Framework\App\RequestInterface $request, // TODO move it out of here
        \Amasty\Reports\Helper\Data $helper,
        DataPersistorInterface $dataPersistor,
        \Amasty\Reports\Model\ResourceModel\RuleIndex $ruleIndex,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
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
        $this->dataPersistor = $dataPersistor;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param $collection
     */
    public function prepareCollection($collection)
    {
        $this->applyBaseFilters($collection);
        $this->applyToolbarFilters($collection);
    }

    /**
     * @param $collection
     */
    public function joinCategoryTable($collection)
    {
        $collection->getSelect()
            ->join(
                ['sales_order_item' => $this->getTable('sales_order_item')],
                'sales_order_item.product_id = main_table.entity_id'
            )
            ->join(
                ['sales_order' => $this->getTable('sales_order')],
                'sales_order_item.order_id = sales_order.entity_id'
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
                'total' => 'SUM(IF(sales_order_item.qty_canceled = 0 '
                    . 'AND sales_order_item.qty_refunded = 0, sales_order_item.row_total, 0))',
                'qty' => 'COUNT(DISTINCT sales_order_item.order_id)',
                'qty_ordered' => 'FLOOR(SUM(sales_order_item.qty_ordered))',
                'qty_canceled' => 'FLOOR(SUM(sales_order_item.qty_canceled))',
                'qty_refunded' => 'FLOOR(SUM(sales_order_item.qty_refunded))',
                'qty_sold' => 'FLOOR(SUM(sales_order_item.qty_ordered)
                                - SUM(sales_order_item.qty_canceled) - SUM(sales_order_item.qty_refunded))',
                'sku' => 'sales_order_item.sku',
                'name' => 'sales_order_item.name',
                'customer_group_id' => 'sales_order.customer_group_id',
                'entity_id' => 'CONCAT(sales_order.entity_id, \'-\', sales_order_item.item_id,  \'-\','
                    . sprintf('eaov1_%1$s.value,', $this->getBrandAttrCode())
                    . '\''.$this->createUniqueEntity().'\')',
                'brand' => sprintf(
                    'eaov1_%1$s.value',
                    $this->getBrandAttrCode()
                )
            ])
        ;
    }

    /**
     * @param $collection
     */
    public function applyToolbarFilters($collection)
    {
        $this->addReportRules($collection);
        $this->addFromFilter($collection, 'sales_order');
        $this->addToFilter($collection, 'sales_order');
        $this->addStoreFilter($collection, 'sales_order');
        $this->addGroupBy($collection);
        $this->addBrandInfo($collection);
    }

    /**
     * @param $collection
     */
    public function addGroupBy($collection)
    {
        $collection->getSelect()
            ->group("sales_order_item.sku");
    }

    /**
     * @param $collection
     */
    private function addBrandInfo($collection)
    {
        $this->joinCustomAttribute($collection, $this->getBrandAttrCode(), 'sales_order_item');
    }

    /**
     * @return string
     */
    private function getBrandAttrCode()
    {
        return $this->scopeConfig->getValue(
            'amasty_reports/general/report_brand',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}

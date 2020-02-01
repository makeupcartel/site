<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\ResourceModel\Catalog\ByBrands;

use Amasty\Reports\Traits\Filters;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Amasty\Reports\Model\ResourceModel\Catalog\ByBrands
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Order\Item\Collection
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
     * @var string
     */
    protected $_idFieldName = '';

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
        \Magento\Framework\App\RequestInterface $request,
        \Amasty\Reports\Helper\Data $helper,
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
            $connection,
            $resource
        );
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
    }

    /**
     * @param $collection
     */
    public function prepareCollection($collection)
    {
        $this->joinOrderTable($collection);
        $this->joinEavAttribute($collection);
        $this->joinChilds($collection);
        $this->applyToolbarFilters($collection);
    }

    /**
     * @param $collection
     */
    public function joinOrderTable($collection)
    {
        $collection->getSelect()->join(
            ['sales_order' => $this->getTable('sales_order')],
            'main_table.order_id = sales_order.entity_id'
        );
    }

    /**
     * @param $collection
     */
    public function joinEavAttribute($collection)
    {
        $eav = $this->scopeConfig->getValue(
            'amasty_reports/general/report_brand',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $entityId = sprintf(
            'CONCAT(eaov1_%1$s.value,\'' . $this->createUniqueEntity() . '\')',
            $eav
        );
        $this->joinCustomAttribute($collection, $eav);

        $collection->getSelect()
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns([
                'name' => sprintf('eaov1_%1$s.value', $eav),
                'total_orders' => 'COUNT(DISTINCT main_table.order_id)',
                'qty' => 'FLOOR(SUM(main_table.qty_ordered))',
                'items_ordered' => 'COUNT(main_table.qty_ordered)',
                'total' => 'SUM(IF(soi.total IS NOT NULL AND soi.total != 0, soi.total, main_table.base_row_total))',
                'tax' => 'SUM(main_table.base_tax_amount)',
                'discounts' => 'SUM(IF(soi.base_discount_amount IS NOT NULL AND soi.base_discount_amount != 0, '
                    . 'soi.base_discount_amount, main_table.base_discount_amount))',
                'invoiced' => 'SUM(IF(soi.invoiced IS NOT NULL AND soi.invoiced != 0, '
                    . 'soi.invoiced, main_table.base_row_invoiced))',
                'refunded' => 'SUM(IF(soi.refunded IS NOT NULL AND soi.refunded != 0, '
                    . 'soi.refunded, main_table.base_amount_refunded))',
                'entity_id' => $entityId
            ])->where(
                'main_table.parent_item_id IS NULL AND ' .
                sprintf('(eaov1_%1$s.value IS NOT NULL)', $eav)
            );
    }

    /**
     * @param $collection
     */
    public function applyToolbarFilters($collection)
    {
        $this->addFromFilter($collection, 'sales_order');
        $this->addToFilter($collection, 'sales_order');
        $this->addStoreFilter($collection, 'sales_order');
    }

    /**
     * @param AbstractCollection $collection
     */
    private function joinChilds($collection)
    {
        $childsSelect = $this->getConnection()->select()->from(
            $this->getTable('sales_order_item'),
            [
                'total' => 'SUM(base_row_total)',
                'invoiced' => 'SUM(base_row_invoiced)',
                'refunded' => 'SUM(base_amount_refunded)',
                'base_discount_amount' => 'SUM(base_discount_amount)',
                'parent_item_id'
            ]
        )->group(
            'parent_item_id'
        );

        $collection->getSelect()->joinLeft(
            ['soi' => $childsSelect],
            'soi.parent_item_id = main_table.item_id',
            ''
        );
    }
}

<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\ResourceModel\Sales\OrderItems;

use Amasty\Reports\Traits\Filters;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
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
     * @param AbstractCollection $collection
     */
    public function prepareCollection($collection)
    {
        $this->applyBaseFilters($collection);
        $this->joinChilds($collection);
        $this->applyToolbarFilters($collection);
    }

    /**
     * @param AbstractCollection $collection
     */
    public function joinOrderTable($collection)
    {
        $collection->getSelect()->join(
            ['sales_order' => $this->getTable('sales_order_grid')],
            'main_table.order_id = sales_order.entity_id'
        );
    }

    /**
     * @param AbstractCollection $collection
     */
    public function applyBaseFilters($collection)
    {
        $this->joinOrderTable($collection);
        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns([
            'increment_id' => 'sales_order.increment_id',
            'order_status' => 'sales_order.status',
            'order_date' => 'sales_order.created_at',
            'payment_method' => 'sales_order.payment_method',
            'product_sku' => 'main_table.sku',
            'product_name' => 'main_table.name',
            'orig_price' => 'main_table.base_original_price',
            'price' => 'main_table.base_price',
            'qty' => 'FLOOR(main_table.qty_ordered)',
            'subtotal' => 'IF(soi.subtotal IS NOT NULL AND soi.subtotal != 0, soi.subtotal, main_table.base_row_total)',
            'tax' => 'main_table.base_tax_amount',
            'discounts' => 'IF(soi.base_discount_amount IS NOT NULL AND soi.base_discount_amount != 0, '
                . 'soi.base_discount_amount, main_table.base_discount_amount)',
            'row_total' => 'IF(soi.row_total IS NOT NULL AND soi.row_total != 0, '
                . 'soi.row_total, main_table.base_row_total_incl_tax)',
            'order_id' => 'sales_order.entity_id',
            'product_id' => 'main_table.product_id'
        ])->where('main_table.parent_item_id IS NULL');
    }

    /**
     * @param AbstractCollection $collection
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
                'subtotal' => 'SUM(base_row_total)',
                'row_total' => 'SUM(base_row_total_incl_tax)',
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

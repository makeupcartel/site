<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\ResourceModel\Sales\Country;

use Amasty\Reports\Traits\Filters;

/**
 * Class Collection
 * @package Amasty\Reports\Model\ResourceModel\Sales\Country
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

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\DB\Helper $coreResourceHelper,
        \Magento\Framework\App\RequestInterface $request, // TODO move it out of here
        \Amasty\Reports\Helper\Data $helper,
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
    }

    /**
     * @param \Amasty\Reports\Model\ResourceModel\Sales\Country\Grid\Collection $collection
     */
    public function prepareCollection($collection)
    {
        $this->applyBaseFilters($collection);
        $this->applyToolbarFilters($collection);
    }

    /**
     * @param $collection
     */
    public function joinAddressTable($collection)
    {
        $collection->getSelect()
            ->joinLeft(
                ['sales_order_address' => $this->getTable('sales_order_address')],
                'sales_order_address.parent_id = main_table.entity_id',
                []
            )
            ->where('address_type = "billing"')
        ;
    }

    /**
     * @param $collection
     */
    public function applyBaseFilters($collection)
    {
        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $this->joinAddressTable($collection);
        $collection->getSelect()->columns([
            'period' =>'sales_order_address.country_id',
            'total_orders' => 'COUNT(main_table.entity_id)',
            'total_items' => 'SUM(main_table.total_item_count)',
            'subtotal' => 'SUM(main_table.base_subtotal)',
            'tax' => 'SUM(main_table.base_tax_amount)',
            'status' => 'main_table.status',
            'shipping' => 'SUM(main_table.base_shipping_amount)',
            'discounts' => 'SUM(main_table.base_discount_amount)',
            'total' => 'SUM(main_table.base_grand_total)',
            'invoiced' => 'IFNULL(SUM(main_table.base_total_invoiced), 0)',
            'refunded' => 'IFNULL(SUM(main_table.base_total_refunded), 0)',
            'entity_id' => 'CONCAT(main_table.entity_id,sales_order_address.country_id, \''
                . $this->createUniqueEntity() . '\')'
        ]);
    }

    /**
     * @param $collection
     */
    public function applyToolbarFilters($collection)
    {
        $this->addFromFilter($collection);
        $this->addToFilter($collection);
        $this->addStoreFilter($collection);
        $this->addGroupBy($collection);
        $this->addStatusFilter($collection);
    }

    /**
     * @param $collection
     */
    public function addGroupBy($collection)
    {
        $collection->getSelect()->group("sales_order_address.country_id");
    }
}

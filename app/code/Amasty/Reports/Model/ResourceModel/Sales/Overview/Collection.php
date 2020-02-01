<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\ResourceModel\Sales\Overview;

use Amasty\Reports\Traits\Filters;

/**
 * Class Collection
 * @package Amasty\Reports\Model\ResourceModel\Sales\Overview
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
     * Collection constructor.
     *
     * @param \Magento\Framework\Data\Collection\EntityFactory                  $entityFactory
     * @param \Psr\Log\LoggerInterface                                          $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface      $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface                         $eventManager
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot
     * @param \Magento\Framework\DB\Helper                                      $coreResourceHelper
     * @param \Magento\Framework\App\RequestInterface                           $request
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null               $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null         $resource
     */
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
     * @param \Amasty\Reports\Model\ResourceModel\Sales\Overview\Grid\Collection $collection
     * @return \Amasty\Reports\Model\ResourceModel\Sales\Overview\Grid\Collection $collection
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
    public function applyBaseFilters($collection)
    {
        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collection->getSelect()
            ->columns([
                    'total_orders' => 'COUNT(entity_id)',
                    'total_items'  => 'SUM(total_item_count)',
                    'subtotal'     => 'SUM(base_subtotal)',
                    'tax'          => 'SUM(base_tax_amount)',
                    'shipping'     => 'SUM(base_shipping_amount)',
                    'discounts'    => 'SUM(base_discount_amount)',
                    'total'        => 'SUM(base_grand_total)',
                    'invoiced'     => 'IFNULL(SUM(base_total_invoiced), 0)',
                    'refunded'     => 'IFNULL(SUM(base_total_refunded), 0)'
            ]);
        if ($collection->getFlag('force_sorting')) {
            $collection->getSelect()->order('period ' . \Magento\Framework\DB\Select::SQL_ASC);
        }
    }

    /**
     * @param $collection
     */
    public function applyToolbarFilters($collection)
    {
        $this->addFromFilter($collection);
        $this->addToFilter($collection);
        $this->addStoreFilter($collection);
        $this->addInterval($collection);
        $this->addStatusFilter($collection);
    }

    /**
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult $collection
     *
     * @return array
     */
    public function getTotals($collection)
    {
        $collection->_renderFilters()->_renderOrders()->_renderLimit();
        $rowsSelect = clone $collection->getSelect();
        $totalsSelect = $this->getConnection()->select()->from(['rows' => $rowsSelect], [
            'total_orders' => 'SUM(total_orders)',
            'total_items' => 'SUM(total_items)',
            'subtotal' => 'SUM(subtotal)',
            'tax' => 'SUM(tax)',
            'shipping' => 'SUM(shipping)',
            'discounts' => 'SUM(discounts)',
            'total' => 'SUM(total)',
            'invoiced' => 'SUM(invoiced)',
            'refunded' => 'SUM(refunded)'
        ]);

        return $this->getConnection()->fetchRow($totalsSelect);
    }
}

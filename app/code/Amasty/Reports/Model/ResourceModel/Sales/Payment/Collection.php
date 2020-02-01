<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\ResourceModel\Sales\Payment;

use Amasty\Reports\Traits\Filters;

/**
 * Class Collection
 * @package Amasty\Reports\Model\ResourceModel\Sales\Payment
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
     * @var \Magento\Payment\Helper\Data
     */
    private $paymentHelper;

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
        \Magento\Payment\Helper\Data $paymentHelper,
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
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * @param \Amasty\Reports\Model\ResourceModel\Sales\Payment\Grid\Collection $collection
     */
    public function prepareCollection($collection)
    {
        $this->applyBaseFilters($collection);
        $this->applyToolbarFilters($collection);
    }

    /**
     * @param $collection
     */
    public function joinPaymentTable($collection)
    {
        $collection->getSelect()
            ->columns(['period' => 'payment.method'])
            ->join(
                ['payment' => $this->getTable('sales_order_payment')],
                'payment.parent_id = main_table.entity_id'
            )
        ;
    }

    /**
     * @param $collection
     */
    public function applyBaseFilters($collection)
    {
        $collection->getSelect()
            ->reset(\Zend_Db_Select::COLUMNS);
        $this->joinPaymentTable($collection);
        $collection->getSelect()
            ->columns([
                'total_orders' => 'COUNT(main_table.entity_id)',
                'total_items' => 'SUM(main_table.total_item_count)',
                'subtotal' => 'SUM(main_table.base_subtotal)',
                'tax' => 'SUM(main_table.base_tax_amount)',
                'status' => 'main_table.status',
                'shipping' => 'SUM(main_table.base_shipping_amount)',
                'discounts' => 'SUM(main_table.base_discount_amount)',
                'total' => 'SUM(main_table.base_grand_total)',
                'invoiced' => 'SUM(main_table.base_total_invoiced)',
                'refunded' => 'SUM(main_table.base_total_refunded)',
                'entity_id' => 'CONCAT(main_table.entity_id,\''.$this->createUniqueEntity().'\')'
            ])
        ;
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
        $collection->getSelect()
            ->group("payment.method")
        ;
    }

    /**
     * @return $this|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected function _afterLoad()
    {
        parent::_afterLoad(); // TODO: Change the autogenerated stub

        $paymentMethods = $this->paymentHelper->getPaymentMethods();
        foreach ($this->_items as $item) {
            $item->setPeriod($paymentMethods[$item->getPeriod()]['title']);
        }
        return $this;
    }
}

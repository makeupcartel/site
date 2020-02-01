<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\ResourceModel\Sales\Compare;

use Amasty\Reports\Traits\Filters;

/**
 * Class Collection
 * @package Amasty\Reports\Model\ResourceModel\Sales\Compare
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Order\Collection
{
    use Filters;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Amasty\Reports\Helper\Data
     */
    private $helper;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\DB\Helper $coreResourceHelper,
        \Magento\Framework\App\RequestInterface $request,
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
     * @param null $from
     * @param null $to
     * @return $this
     */
    public function prepareCollection($from = null, $to = null)
    {
        $this->applyBaseFilters();
        $this->addFromFilter($from);
        $this->addToFilter($to);
        $this->addStoreFilter($this);
        $this->addInterval($this);
        $this->addStatusFilter($this);

        return $this;
    }

    /**
     * @return void
     */
    public function applyBaseFilters()
    {
        $this->getSelect()
            ->columns([
                'total_orders' => 'COUNT(entity_id)',
                'total_items' => 'SUM(total_item_count)',
                'subtotal' => 'SUM(base_subtotal)',
                'tax' => 'SUM(base_tax_amount)',
                'shipping' => 'SUM(base_shipping_amount)',
                'discounts' => 'SUM(base_discount_amount)',
                'total' => 'SUM(base_grand_total)',
                'invoiced' => 'SUM(base_total_invoiced)',
                'refunded' => 'SUM(base_total_refunded)',
            ])
            ->order('main_table.created_at ASC');
    }

    /**
     * @param $from
     */
    private function addFromFilter($from)
    {
        $from = $from ?: date('Y-m-d', $this->helper->getDefaultFromDate());
        $from = $this->helper->getDateForLocale($from);
        $this->getSelect()->where('main_table.created_at >= ?', $from);
    }

    /**
     * @param $to
     */
    private function addToFilter($to)
    {
        $to = $to ?: date('Y-m-d', $this->helper->getDefaultToDate());
        $to = $this->helper->getDateForLocale($to, 23, 59, 59);
        $this->getSelect()->where('main_table.created_at <= ?', $to);
    }
}

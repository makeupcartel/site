<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\ResourceModel\Sales\CartRule;

use Amasty\Reports\Traits\Filters;
use Zend_Db_Expr;

/**
 * Class Collection
 * @package Amasty\Reports\Model\ResourceModel\Sales\CartRule
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
     * @param \Amasty\Reports\Model\ResourceModel\Sales\CartRule\Grid\Collection $collection
     */
    public function prepareCollection($collection)
    {
        $this->joinRuleTable($collection);
        $select = clone $collection->getSelect();
        $collection->getSelect()->reset();
        $collection->getSelect()->from(['main_table' => $select]);
        $this->applyBaseFilters($collection);
        $this->applyToolbarFilters($collection);
    }

    /**
     * @param $collection
     */
    public function joinRuleTable($collection)
    {
        $collection->getSelect()
            ->columns([
                'period' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT salesrule.name separator \',\')')
            ])->join(
                ['salesrule' => $this->getTable('salesrule')],
                'find_in_set(salesrule.rule_id, main_table.applied_rule_ids) > 0',
                []
            )->group(
                'main_table.entity_id'
            );
        if ($statuses = $this->getStatusesOrder()) {
            $collection->getSelect()->having(
                'main_table.status IN (?)',
                $statuses
            );
        }
    }

    /**
     * @param $collection
     */
    public function applyBaseFilters($collection)
    {
        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collection->getSelect()
            ->columns([
                'period' => 'period',
                'total_orders' => 'COUNT(entity_id)',
                'total_items' => 'SUM(total_item_count)',
                'subtotal' => 'SUM(base_subtotal)',
                'tax' => 'SUM(base_tax_amount)',
                'status' => 'status',
                'shipping' => 'SUM(base_shipping_amount)',
                'discounts' => 'SUM(base_discount_amount)',
                'total' => 'SUM(base_grand_total)',
                'invoiced' => 'IFNULL(SUM(base_total_invoiced), 0)',
                'refunded' => 'IFNULL(SUM(base_total_refunded), 0)',
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
    }

    /**
     * @param $collection
     */
    public function addGroupBy($collection)
    {
        $collection->getSelect()->group('main_table.applied_rule_ids');
    }
}

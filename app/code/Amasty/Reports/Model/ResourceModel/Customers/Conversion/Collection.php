<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\ResourceModel\Customers\Conversion;

use Amasty\Reports\Traits\Filters;
use Magento\Sales\Model\Order;

/**
 * Class Collection
 * @package Amasty\Reports\Model\ResourceModel\Customers\Conversion
 */
class Collection extends \Magento\Customer\Model\ResourceModel\Visitor\Collection
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Amasty\Reports\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\RequestInterface $request,
        \Amasty\Reports\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->request = $request;
        $this->helper = $helper;
        $this->dateTime = $dateTime;
    }

    /**
     * @param \Amasty\Reports\Model\ResourceModel\Customers\Conversion\Grid\Collection | $this
     */
    public function prepareCollection($collection)
    {
        $this->applyBaseFilters($collection);
    }

    /**
     * @param \Amasty\Reports\Model\ResourceModel\Customers\Conversion\Grid\Collection | $this
     * @param string $tablePrefix
     */
    public function applyBaseFilters($collection)
    {
        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);

        list($periodSelect, $group) = $this->getIntervalSelectAndGroupBy($collection);

        list($orderPeriodSelect, $orderGroup) = $this->getIntervalSelectAndGroupBy($collection, true);

        $from = $this->getFromFilter();
        $to = $this->getToFilter();

        $convertionExpr = "ROUND(COUNT(DISTINCT orderTable.entity_id) / COUNT(DISTINCT main_table.session_id) * 100)";
        $exculdedStates = [Order::STATE_CANCELED, Order::STATE_CLOSED];
        $collection->getSelect()
            ->columns([
                'period' => $periodSelect,
                'orders' => 'COUNT(DISTINCT orderTable.entity_id)',
                'visitors' => 'COUNT(DISTINCT main_table.session_id)',
                'conversion' => $convertionExpr,
            ])
            ->joinLeft(
                ['orderTable' => $this->getTable('sales_order')],
                $periodSelect . ' = ' . $orderPeriodSelect
                . " AND (orderTable.state NOT IN('" . implode("','", $exculdedStates) . "'))"
                . " AND orderTable.remote_ip IS NOT NULL",
                []
            )
            ->where('(DATE(main_table.last_visit_at) >= ?)', $from)
            ->where('(DATE(main_table.last_visit_at) <= ?)', $to)
            ->order($periodSelect . ' DESC')
            ->group($group);
    }

    /**
     * @param \Amasty\Reports\Model\ResourceModel\Customers\Conversion\Grid\Collection | $this
     * @return array
     */
    protected function getIntervalSelectAndGroupBy($collection, $isOrder = false)
    {
        $field = $isOrder ? 'orderTable.created_at' : 'main_table.last_visit_at';
        $filters = $this->request->getParam('amreports');
        $interval = isset($filters['interval']) ? $filters['interval'] : 'day';
        $collection->getSelect()->reset(\Zend_Db_Select::GROUP);

        switch ($interval) {
            case 'year':
                $select = $group = sprintf('YEAR(DATE(%s))', $field);
                break;
            case 'month':
                $select = 'CONCAT(YEAR(DATE(%1$s)), \'-\', MONTH(DATE(%1$s)))';
                $select = sprintf($select, $field);
                $group = sprintf('MONTH(DATE(%s))', $field);
                break;
            case 'week':
                $select = 'CONCAT(ADDDATE(DATE(DATE(%1$s)), INTERVAL 1-DAYOFWEEK(DATE(%1$s)) DAY), '
                    . '" - ", ADDDATE(DATE(DATE(%1$s)), INTERVAL 7-DAYOFWEEK(DATE(%1$s)) DAY))';
                $select = sprintf($select, $field);
                $group = sprintf('WEEK(DATE(%s))', $field);
                break;
            case 'day':
            default:
                $select = $group = sprintf('DATE(DATE(%s))', $field);
                break;
        }

        return [$select, $group];
    }

    /**
     * @return false|string
     */
    protected function getFromFilter()
    {
        $filters = $this->request->getParam('amreports');

        return isset($filters['from'])
            ? $filters['from']
            : $this->getDateTime('Y-m-d', $this->helper->getDefaultFromDate());
    }

    /**
     * @return false|string
     */
    protected function getToFilter()
    {
        $filters = $this->request->getParam('amreports');

        return isset($filters['to']) ? $filters['to'] : $this->getDateTime('Y-m-d');
    }

    /**
     * @param string $format
     * @param int|string $input
     *
     * @return mixed
     */
    protected function getDateTime($format = null, $input = null)
    {
        return $this->dateTime->date($format, $input);
    }
}

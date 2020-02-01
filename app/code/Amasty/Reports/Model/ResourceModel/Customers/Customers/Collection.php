<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\ResourceModel\Customers\Customers;

use Magento\Customer\Api\GroupManagementInterface;
use Amasty\Reports\Traits\Filters;

/**
 * Class Collection
 * @package Amasty\Reports\Model\ResourceModel\Customers\Customers
 */
class Collection extends \Magento\Customer\Model\ResourceModel\Customer\Collection
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
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\DataObject\Copy\Config $fieldsetConfig,
        \Magento\Framework\App\RequestInterface $request, // TODO move it out of here
        \Amasty\Reports\Helper\Data $helper,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        $modelName = self::CUSTOMER_MODEL_NAME
    ) {
        $this->_fieldsetConfig = $fieldsetConfig;
        $this->_modelName = $modelName;
        $this->request = $request;
        $this->helper = $helper;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $entitySnapshot,
            $fieldsetConfig,
            $connection,
            $modelName
        );
    }

    /**
     * @param $collection
     * @param string $tablePrefix
     */
    public function prepareCollection($collection, $tablePrefix = 'main_table')
    {
        $this->applyBaseFilters($collection);
        $this->applyToolbarFilters($collection, $tablePrefix);
    }

    /**
     * @param $collection
     */
    public function applyBaseFilters($collection)
    {
        $collection->getSelect()
            ->reset(\Zend_Db_Select::FROM)
        ;
        $this->addTableFilter($collection);
        $collection->getSelect()
            ->reset(\Zend_Db_Select::COLUMNS)
        ;
        $collection->getSelect()
            ->columns([
                'period' => 'period',
                'new_accounts' => 'new_accounts',
                'orders' => 'orders',
                'reviews' => 'reviews',
                'entity_id' => 'CONCAT(period,store_id,\''.$this->createUniqueEntity().'\')'
            ])
        ;
    }

    /**
     * @param $collection
     * @param string $tablePrefix
     */
    public function applyToolbarFilters($collection, $tablePrefix = 'main_table')
    {
        $this->addFromFilter($collection, $tablePrefix);
        $this->addToFilter($collection, $tablePrefix);
        $this->addStoreFilter($collection, $tablePrefix);
        $this->addGroupBy($collection, $tablePrefix);
    }

    /**
     * @param $collection
     */
    public function addTableFilter($collection)
    {
        $filters = $this->request->getParam('amreports');
        $interval = isset($filters['interval']) ? $filters['interval'] : 'day';
        switch ($interval) {
            case 'day':
                $collection->getSelect()->from([
                    'main_table' => $this->getTable('amasty_reports_customers_customers_daily')
                ]);
                break;
            case 'week':
                $collection->getSelect()->from([
                    'main_table' => $this->getTable('amasty_reports_customers_customers_weekly')
                ]);
                break;
            case 'month':
                $collection->getSelect()->from([
                    'main_table' => $this->getTable('amasty_reports_customers_customers_monthly')
                ]);
                break;
            case 'year':
                $collection->getSelect()->from([
                    'main_table' => $this->getTable('amasty_reports_customers_customers_yearly')
                ]);
                break;
        }
    }

    /**
     * @param $collection
     * @param string $tablePrefix
     */
    public function addFromFilter($collection, $tablePrefix = 'main_table')
    {
        $filters = $this->request->getParam('amreports');
        $from = isset($filters['from']) ? $filters['from'] : date('Y-m-d', $this->helper->getDefaultFromDate());
        $interval = isset($filters['interval']) ? $filters['interval'] : 'day';
        switch ($interval) {
            case 'day':
                $expr = 'DATE('.$tablePrefix.'.period) >= ?';
                break;
            case 'week':
                $expr = "CONCAT(YEAR({$tablePrefix}.period), '-', "
                    . "WEEK({$tablePrefix}.period)) >= CONCAT(YEAR(?), '-', WEEK(?))";
                break;
            case 'month':
                $expr = "DATE(CONCAT(YEAR({$tablePrefix}.period), '-', MONTH({$tablePrefix}.period), '-1')) "
                    . ">= DATE(CONCAT(YEAR(?), '-', MONTH(?), '-1'))";
                break;
            case 'year':
                $expr = "YEAR({$tablePrefix}.period) >= YEAR(?)";
                break;
        }
        if ($from) {
            $collection->getSelect()->where($expr, $from);
        }
    }

    /**
     * @param $collection
     * @param string $tablePrefix
     */
    public function addToFilter($collection, $tablePrefix = 'main_table')
    {
        $filters = $this->request->getParam('amreports');
        $to = isset($filters['to']) ? $filters['to'] : date('Y-m-d');
        $interval = isset($filters['interval']) ? $filters['interval'] : 'day';
        switch ($interval) {
            case 'day':
                $expr = 'DATE('.$tablePrefix.'.period) <= ?';
                break;
            case 'week':
                $expr = "CONCAT(YEAR({$tablePrefix}.period), '-', "
                    . "WEEK({$tablePrefix}.period)) <= CONCAT(YEAR(?), '-', WEEK(?))";
                break;
            case 'month':
                $expr = "DATE(CONCAT(YEAR({$tablePrefix}.period), '-', MONTH({$tablePrefix}.period), '-1')) "
                    . "<= DATE(CONCAT(YEAR(?), '-', MONTH(?), '-1'))";
                break;
            case 'year':
                $expr = "YEAR({$tablePrefix}.period) <= YEAR(?)";
                break;
        }
        if ($to) {
            $collection->getSelect()->where($expr, $to);
        }
    }

    /**
     * @param $collection
     * @param $tablePrefix
     */
    public function addGroupBy($collection, $tablePrefix)
    {
        $collection->getSelect()
            ->group("DATE($tablePrefix.period)")
        ;
    }
}

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
 * Class Statistic
 * @package Amasty\Reports\Model\ResourceModel\Customers\Customers
 */
class Statistic extends \Magento\Reports\Model\ResourceModel\Report\AbstractReport
{

    const AGGREGATION_DAILY = 'daily';

    const AGGREGATION_MONTHLY = 'monthly';

    const AGGREGATION_YEARLY = 'yearly';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $_productResource;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Helper
     */
    protected $_salesResourceHelper;

    /**
     * Ignored product types list
     *
     * @var array
     */
    protected $ignoredProductTypes = [
        \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE => \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE,
    ];
    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Reports\Model\FlagFactory $reportsFlagFactory
     * @param \Magento\Framework\Stdlib\DateTime\Timezone\Validator $timezoneValidator
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param \Magento\Sales\Model\ResourceModel\Helper $salesResourceHelper
     * @param array $ignoredProductTypes
     * @param string $connectionName
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Reports\Model\FlagFactory $reportsFlagFactory,
        \Magento\Framework\Stdlib\DateTime\Timezone\Validator $timezoneValidator,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Sales\Model\ResourceModel\Helper $salesResourceHelper,
        $connectionName = null,
        array $ignoredProductTypes = []
    ) {
        parent::__construct(
            $context,
            $logger,
            $localeDate,
            $reportsFlagFactory,
            $timezoneValidator,
            $dateTime,
            $connectionName
        );
        $this->_productResource = $productResource;
        $this->_salesResourceHelper = $salesResourceHelper;
        $this->ignoredProductTypes = array_merge($this->ignoredProductTypes, $ignoredProductTypes);
    }

    /**
     * Aggregate Orders data by order created at
     *
     * @param string|int|\DateTime|array|null $from
     * @param string|int|\DateTime|array|null $to
     * @return $this
     * @throws \Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function aggregate($from = null, $to = null)
    {
        $connection = $this->getConnection();

        $dayTable = $this->getTable('amasty_reports_customers_customers_daily');
        $weekTable = $this->getTable('amasty_reports_customers_customers_monthly');
        $monthTable = $this->getTable('amasty_reports_customers_customers_weekly');
        $yearTable = $this->getTable('amasty_reports_customers_customers_yearly');
        try {
            $this->clearOldData($dayTable);
            $this->clearOldData($weekTable);
            $this->clearOldData($monthTable);
            $this->clearOldData($yearTable);

            $this->populateStatistic($dayTable, 'day');
            $this->populateStatistic($weekTable, 'month');
            $this->populateStatistic($monthTable, 'week');
            $this->populateStatistic($yearTable, 'year');

            $this->_setFlagData(\Amasty\Reports\Model\Flag::REPORT_CUSTOMERS_CUSTOMERS_FLAG_CODE);

        } catch (\Exception $e) {
            throw $e;
        }

        return $this;
    }

    /**
     * @param $table
     * @param $period
     */
    protected function populateStatistic($table, $period)
    {
        $connection = $this->getConnection();

        $customers = $this->getCustomersData($connection, $period);
        $orders = $this->getSalesData($connection, $period);
        $reviews = $this->getReviewsData($connection, $period);
        $result = $this->mergeArrays($customers, $orders, $reviews);

        $this->insertAggregated($table, $result);
    }

    /**
     * @param $connection
     * @param $period
     * @return mixed
     */
    protected function getCustomersData($connection, $period)
    {
        $customers = $connection->select()
            ->from($this->getTable('customer_entity'))
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns([
                'store_id' => "store_id",
                'count' => 'COUNT(entity_id)'
            ]);
        $this->getFormat($customers, $period);
        $customers->group('store_id');
        $customers = $connection->fetchAll($customers);
        return $customers;
    }

    /**
     * @param $connection
     * @param $period
     * @return mixed
     */
    protected function getSalesData($connection, $period)
    {
        $sales = $connection->select()
            ->from($this->getTable('sales_order'))
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns([
                'store_id' => "store_id",
                'count' => 'COUNT(entity_id)'
            ])
        ;
        $this->getFormat($sales, $period);
        $sales->where('customer_id IS NOT NULL');
        $sales->group('store_id');
        $sales = $connection->fetchAll($sales);
        return $sales;
    }

    /**
     * @param $connection
     * @param $period
     * @return mixed
     */
    protected function getReviewsData($connection, $period)
    {
        $reviews = $connection->select()
            ->from(['review' => $this->getTable('review')])
            ->join(
                ['rs' => $this->getTable('review_store')],
                "rs.review_id = review.entity_id"
            )
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns([
                'store_id' => "rs.store_id",
                'count' => 'COUNT(entity_id)'
            ])
        ;
        $this->getFormat($reviews, $period);
        $reviews->group('rs.store_id');
        $reviews = $connection->fetchAll($reviews);
        return $reviews;
    }

    /**
     * @param $select
     * @param $period
     */
    protected function getFormat($select, $period)
    {
        switch ($period) {
            case 'year':
                $select
                    ->columns([
                        'date' => "CONCAT(YEAR(created_at), '-01-01')",
                    ])
                    ->group("YEAR(created_at)")
                ;
                break;
            case 'month':
                $select
                    ->columns([
                        'date' => "CONCAT(YEAR(created_at), '-', MONTH(created_at), '-1')",
                    ])
                    ->group("MONTH(created_at)")
                ;
                break;
            case 'week':
                $select
                    ->columns([
                        'date' => "CONCAT(ADDDATE(DATE(created_at), INTERVAL 1-DAYOFWEEK(created_at) DAY))",
                    ])
                    ->group("CONCAT(YEAR(created_at), '-', WEEK(created_at))")
                ;
                break;
            case 'day':
            default:
                $select
                    ->columns([
                        'date' => "DATE(created_at)",
                    ])
                    ->group('DATE(created_at)')
                ;
        }
    }

    /**
     * @param $table
     * @param $data
     */
    protected function insertAggregated($table, $data)
    {
        $connection = $this->getConnection();
        $result = [];

        foreach ($data as $date => $store) {
            foreach ($store as $storeId => $item) {
                $parts = [];
                $parts['new_accounts'] = isset($item['customers']) ? $item['customers']['count'] : '';
                $parts['orders'] = isset($item['orders']) ? $item['orders']['count'] : '';
                $parts['reviews'] = isset($item['reviews']) ? $item['reviews']['count'] : '';
                //@codingStandardsIgnoreLine
                $result[] = array_merge(['store_id' => $storeId, 'period' => $date], $parts);
            }
        }
        $connection->insertMultiple($table, $result);
    }

    /**
     * @param $array1
     * @param $array2
     * @param $array3
     * @return array
     */
    protected function mergeArrays($array1, $array2, $array3)
    {
        $result = [];
        foreach ($array1 as $item) {
            $result[$item['date']][$item['store_id']]['customers'] = $item;
        }
        foreach ($array2 as $item) {
            $result[$item['date']][$item['store_id']]['orders'] = $item;
        }
        foreach ($array3 as $item) {
            $result[$item['date']][$item['store_id']]['reviews'] = $item;
        }
        return $result;
    }

    /**
     * @param $table
     * @return void
     */
    protected function clearOldData($table)
    {
        $this->getConnection()->delete($table);
    }
    
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amasty_reports_customers_customers_daily', 'id');
    }
}

<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\ResourceModel\Report;

use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\Timezone\Validator;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\FlagFactory;
use Magento\Reports\Model\ResourceModel\Helper;
use Magento\Sales\Model\ResourceModel\Report\AbstractReport;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Order;

/**
 * Class Dashboard
 * @package Amasty\Reports\Model\ResourceModel\Report
 */
class Dashboard extends AbstractReport
{
    /**
     * Aggregation key daily
     */
    const AGGREGATION_DAILY = 'report_viewed_product_aggregated_daily';

    /**
     * Aggregation key monthly
     */
    const AGGREGATION_MONTHLY = 'report_viewed_product_aggregated_monthly';

    /**
     * Aggregation key yearly
     */
    const AGGREGATION_YEARLY = 'report_viewed_product_aggregated_yearly';

    /**
     * Product resource instance
     *
     * @var Product
     */
    protected $_productResource;

    /**
     * Resource helper instance
     *
     * @var Helper
     */
    protected $_resourceHelper;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var Collection
     */
    private $productCollection;

    /**
     * @var \Amasty\Reports\Helper\Data
     */
    private $dataHelper;

    /**
     * @var \Amasty\Reports\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Visitor\CollectionFactory
     */
    private $visitorCollection;

    public function __construct(
        Context $context,
        LoggerInterface $logger,
        TimezoneInterface $localeDate,
        FlagFactory $reportsFlagFactory,
        \Amasty\Reports\Helper\Data $dataHelper,
        Validator $timezoneValidator,
        DateTime $dateTime,
        Product $productResource,
        Collection $productCollection,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        Helper $resourceHelper,
        \Amasty\Reports\Helper\Data $helper,
        \Magento\Customer\Model\ResourceModel\Visitor\CollectionFactory $visitorCollection,
        $connectionName = null
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
        $this->_resourceHelper = $resourceHelper;
        $this->timezone = $timezone;
        $this->dateTime = $dateTime;
        $this->productCollection = $productCollection;
        $this->dataHelper = $dataHelper;
        $this->helper = $helper;
        $this->visitorCollection = $visitorCollection;
    }

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('report_event', 'id');
    }

    /**
     * @param null $from
     * @param null $to
     * @return array
     */
    public function getFunnel($from = null, $to = null)
    {
        $productViewedIds = $this->getViewedProducts($from, $to);
        $productAddedIds = $this->getAddedProducts($from, $to);
        $placedOrdersCount = $this->getPlacedOrdersCount($from, $to);
        $uniqueVisitors = $this->getUniqueCustomerVisits($from, $to);
        if (($uniqueVisitors - $placedOrdersCount) > 0) {
            $lostOrdersCount = $uniqueVisitors - $placedOrdersCount;
        } else {
            $lostOrdersCount = 0;
        }
        $viewedCount = count($productViewedIds);
        $addedCount = count($productAddedIds);
        $allCount = $this->getProductCount();
        $orderedProducts = $this->getOrderedProducts($from, $to);
        $orderedCount= count($orderedProducts);
        $notViewed = count(array_diff($productViewedIds, $productAddedIds));
        $viewedCount -= $notViewed;
        $viewedPercent = $this->getPercent($viewedCount, $notViewed);
        $abandoned = count(array_diff($productAddedIds, $orderedProducts));
        $addedPercent = $this->getPercent($abandoned, $orderedCount);
        $ordersPercent = $this->getPercent($placedOrdersCount, $lostOrdersCount);

        return [
            'viewedCount' => round($viewedCount),
            'addedCount' => $addedCount,
            'allCount' => $allCount,
            'orderedCount' => $orderedCount,
            'notViewed' => $notViewed,
            'viewedPercent' => round($viewedPercent),
            'addedPercent' => round($addedPercent),
            'abandoned' => $abandoned,
            'ordersPercent' => round($ordersPercent),
            'uniqueVisitors' => $this->getUniqueCustomerVisits($from, $to),
            'placedOrdersCount' => $placedOrdersCount,
            'lostOrdersCount' => $lostOrdersCount,
        ];
    }

    /**
     * @param $firstValue
     * @param $secondValue
     * @return float|int
     */
    private function getPercent($firstValue, $secondValue)
    {
        $result = 0;
        if ($firstValue) {
            $result = $firstValue / ($secondValue + $firstValue) * 100;
        }

        return $result;
    }

    /**
     * @param $from
     * @param $to
     * @return mixed
     */
    private function getPlacedOrdersCount($from, $to)
    {
        $from = $this->helper->getDateForLocale($from);
        $to = $this->helper->getDateForLocale($to, 23, 59, 59);

        $select = $this->getConnection()->select();

        $select->from(['main_table' => $this->getTable('sales_order')], [])
            ->where(
                'main_table.state NOT IN(?)',
                [Order::STATE_CANCELED, Order::STATE_CLOSED]
            )
            ->where('main_table.created_at >= ?', $from)
            ->where('main_table.remote_ip IS NOT NULL')
            ->where('main_table.created_at <= ?', $to)
            ->columns(['total_orders' => 'COUNT(DISTINCT main_table.entity_id)']);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * @param $from
     * @param $to
     * @return mixed
     */
    private function getUniqueCustomerVisits($from, $to)
    {
        $from = $this->helper->getDateForLocale($from);
        $to = $this->helper->getDateForLocale($to, 23, 59, 59);

        $visitorsCollection = $this->visitorCollection->create()
            ->addFieldToFilter('last_visit_at', ['gteq' => $from])
            ->addFieldToFilter('last_visit_at', ['lteq' => $to]);
        $visitorsCollection->getSelect()->group('session_id');
        return $visitorsCollection->getSize();
    }

    /**
     * @param null $store
     * @return int
     */
    protected function getProductCount($store = null)
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $size = $this->productCollection
            ->addStoreFilter($store)
            ->setVisibility([
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG,
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH,
                ])
            ->getSize();

        return $size;
    }

    /**
     * @param $from
     * @param $to
     *
     * @return array
     */
    protected function getViewedProducts($from, $to)
    {
        $connection = $this->getConnection();
        $from = $this->dateTime->date('Y-m-d', $from);
        $to = $this->dateTime->date('Y-m-d', $to);
        $select = $connection->select();
        $select->from(
            ['source_table' => $this->getTable('report_event')],
            ['object_id']
        )->where(
            'source_table.event_type_id = ?',
            \Magento\Reports\Model\Event::EVENT_PRODUCT_VIEW
        )->where(
            'DATE(source_table.logged_at) >= ?',
            $from
        )->where(
            'DATE(source_table.logged_at) <= ?',
            $to
        );
        if ($storeId = $this->dataHelper->getCurrentStoreId()) {
            $select->where('store_id = ?', $storeId);
        }
        $select->group('source_table.object_id');

        $productIds = $connection->fetchCol($select);

        return $productIds;
    }

    /**
     * @param $from
     * @param $to
     *
     * @return array
     */
    protected function getAddedProducts($from, $to)
    {
        $connection = $this->getConnection();
        $from = $this->dateTime->date('Y-m-d', $from);
        $to = $this->dateTime->date('Y-m-d', $to);
        $select = $connection->select();
        $select->from(
            ['source_table' => $this->getTable('report_event')],
            ['object_id']
        )->where(
            'source_table.event_type_id = ?',
            \Magento\Reports\Model\Event::EVENT_PRODUCT_TO_CART
        )->where(
            'DATE(source_table.logged_at) >= ?',
            $from
        )->where(
            'DATE(source_table.logged_at) <= ?',
            $to
        );
        if ($storeId = $this->dataHelper->getCurrentStoreId()) {
            $select->where('store_id = ?', $storeId);
        }
        $select->group('source_table.object_id');

        $productIds = $connection->fetchCol($select);

        return $productIds;
    }

    /**
     * @param $from
     * @param $to
     *
     * @return array
     */
    protected function getOrderedProducts($from, $to)
    {
        $connection = $this->getConnection();
        $from = $this->dateTime->date('Y-m-d', $from);
        $to = $this->dateTime->date('Y-m-d', $to);
        $exculdedStates = [Order::STATE_CANCELED, Order::STATE_CLOSED];
        $select = $connection->select();
        $select->from(
            ['source_table' => $this->getTable('sales_order_item')],
            ['product_id']
        )->joinInner(
            ['order_table' => $this->getTable('sales_order')],
            "order_table.entity_id = source_table.order_id"
                    . " AND order_table.state NOT IN('" . implode("','", $exculdedStates) . "')"
                    . " AND order_table.remote_ip IS NOT NULL",
            []
        )->where(
            'DATE(source_table.created_at) >= ?',
            $from
        )->where(
            'DATE(source_table.created_at) <= ?',
            $to
        )->where(
            'source_table.parent_item_id IS NULL'
        );
        if ($storeId = $this->dataHelper->getCurrentStoreId()) {
            $select->where('source_table.store_id = ?', $storeId);
        }
        $productsIds = $connection->fetchCol($select);

        return $productsIds;
    }

    /**
     * @param $from
     * @param $to
     * @return mixed
     */
    protected function getAbandonedCart($from, $to)
    {
        $connection = $this->getConnection();
        $from = $this->dateTime->date('Y-m-d', $from);
        $to = $this->dateTime->date('Y-m-d', $to);
        $select = $connection->select();
        $viewsNumExpr = new \Zend_Db_Expr('COUNT(source_table.total_qty_ordered)');
        $columns = [
            'ordered_num' => $viewsNumExpr,
        ];
        $select->from(
            ['source_table' => $this->getTable('sales_order')],
            $columns
        )->where(
            'DATE(source_table.created_at) >= ?',
            $from
        )->where(
            'DATE(source_table.created_at) <= ?',
            $to
        )->where(
            'DATE(source_table.created_at) <= ?',
            $to
        )->where(
            'state = ?',
            \Magento\Sales\Model\Order::STATE_PROCESSING
        );
        if ($storeId = $this->dataHelper->getCurrentStoreId()) {
            $select->where('store_id = ?', $storeId);
        }
        $row = $connection->fetchRow($select);

        return $row['ordered_num'];
    }
}

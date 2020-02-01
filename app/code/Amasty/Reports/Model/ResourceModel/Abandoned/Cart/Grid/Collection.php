<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\ResourceModel\Abandoned\Cart\Grid;

use Amasty\Reports\Model\ResourceModel\Abandoned\Cart\Collection as CartCollection;
use Amasty\Reports\Model\Source\Status;
use Amasty\Reports\Traits\Filters;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;

/**
 * Class Collection
 */
class Collection extends CartCollection implements SearchResultInterface
{
    use Filters;

    /**
     * @var AggregationInterface
     */
    private $aggregations;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Amasty\Reports\Helper\Data
     */
    protected $helper;

    public function __construct(
        \Amasty\Reports\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Amasty\Reports\Model\ResourceModel\Abandoned\Cart $resourceModel,
        $mainTable = \Amasty\Reports\Model\ResourceModel\Abandoned\Cart::MAIN_TABLE,
        $eventPrefix = 'amasty_report_abandoned_carts',
        $eventObject = 'amasty_report_abandoned_carts',
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        $connection = null,
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
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
        $this->request = $request;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * {@inheritdoc}
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    protected function _renderFiltersBefore()
    {
        $this->addFieldToFilter(
            \Amasty\Reports\Model\ResourceModel\Abandoned\Cart::STATUS,
            Status::PROCESSING
        );
        $this->applyToolbarFilters($this);
        parent::_renderFiltersBefore();
    }

    public function prepareCollection($collection)
    {
        $this->updateColumns();
        /** toolbar filter applied only for diagram */
        $this->addInterval($collection);
    }

    /**
     * @param CartCollection $collection
     */
    private function applyToolbarFilters($collection)
    {
        $this->addFromFilter($collection);
        $this->addToFilter($collection);
        $this->addStoreFilter($collection);
    }

    private function updateColumns()
    {
        $this->getSelect()->columns('COUNT(*) as count');
    }
}

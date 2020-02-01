<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model;

use Amasty\Reports\Model\ResourceModel\Sales\Overview\Collection;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Reports\Model\ResourceModel\Order\CollectionFactory;

/**
 * Class Dashboard
 * @package Amasty\Reports\Model
 */
class Dashboard extends AbstractModel
{
    const LAST_ORDER_COUNT = 10;
    
    const BESTSELLERS_COUNT = 10;

    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var ResourceModel\Sales\Overview\Collection
     */
    private $salesCollection;
    /**
     * @var \Amasty\Reports\Helper\Data
     */
    private $reportsHelper;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory
     */
    private $bestsellersCollectionFactory;
    /**
     * @var ResourceModel\Catalog\Bestsellers\Collection
     */
    private $bestsellersCollection;

    public function __construct(
        Context $context,
        Registry $registry,
        RequestInterface $request,
        CollectionFactory $collectionFactory,
        Collection $salesCollection,
        \Amasty\Reports\Helper\Data $reportsHelper,
        \Amasty\Reports\Model\ResourceModel\Catalog\Bestsellers\Collection $bestsellersCollection,
        \Amasty\Reports\Model\ResourceModel\Catalog\Bestsellers\CollectionFactory $bestsellersCollectionFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->request = $request;
        $this->collectionFactory = $collectionFactory;
        $this->salesCollection = $salesCollection;
        $this->reportsHelper = $reportsHelper;
        $this->bestsellersCollectionFactory = $bestsellersCollectionFactory;
        $this->bestsellersCollection = $bestsellersCollection;
    }
    
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\Reports\Model\ResourceModel\Report\Dashboard::class);
    }

    /**
     * @param null $from
     * @param null $to
     * @return mixed
     */
    public function getConversionFunnel($from = null, $to = null)
    {
        return $this->getResource()->getFunnel($from, $to);
    }

    /**
     * @return \Magento\Reports\Model\ResourceModel\Order\Collection
     */
    public function getLastOrders()
    {
        $collection = $this->collectionFactory->create()
            ->addItemCountExpr()
            ->joinCustomerName('customer')
            ->orderByCreatedAt()
            ->setPageSize(self::LAST_ORDER_COUNT);
        if ($this->reportsHelper->getCurrentStoreId()) {
            $collection->addFieldToFilter('store_id', $this->reportsHelper->getCurrentStoreId());
        }
        return $collection;
    }

    /**
     * @return mixed
     */
    public function getBestsellers()
    {
        $collection = $this->bestsellersCollection
            ->prepareCollection($this->bestsellersCollectionFactory->create())
            ->setPageSize(self::BESTSELLERS_COUNT);

        return $collection;
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getSalesCollection()
    {
        $collection = $this->getCollection();
        $this->salesCollection->getDashboardCollection($collection);
        return $collection;
    }
}

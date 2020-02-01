<?php
namespace Convert\SwarmingSubscribePro\Model;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use SubscribePro\Exception\HttpException;

class Product
{
    /**
     * @var \Swarming\SubscribePro\Model\Config\General
     */
    protected $generalConfig;
    
    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;
    
    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $productVisibility;
    
    /**
     * @var \Swarming\SubscribePro\Platform\Manager\Product
     */
    protected $platformProductManager;
    
    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;
    
    /**
     * @var \Swarming\SubscribePro\Helper\Product
     */
    protected $productHelper;
    
    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    protected $websiteRepository;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    
    /**
     *
     * @param \Swarming\SubscribePro\Model\Config\General $generalConfig
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Swarming\SubscribePro\Platform\Manager\Product $platformProductManager
     * @param \Magento\Framework\App\State $state
     * @param \Swarming\SubscribePro\Helper\Product $productHelper
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Swarming\SubscribePro\Model\Config\General $generalConfig,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Swarming\SubscribePro\Platform\Manager\Product $platformProductManager,
        \Magento\Framework\App\State $state,
        \Swarming\SubscribePro\Helper\Product $productHelper,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->generalConfig = $generalConfig;
        $this->productRepository = $productRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productVisibility = $productVisibility;
        $this->platformProductManager = $platformProductManager;
        $this->state = $state;
        $this->productHelper = $productHelper;
        $this->websiteRepository = $websiteRepository;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    public function syncProductToSubscribePro()
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addAttributeToFilter('subscription_enabled', array('eq'=> 1));
        $productCollection->setVisibility($this->productVisibility->getVisibleInSiteIds());
        foreach($productCollection as $_product){
            foreach($_product->getWebsiteIds() as $websiteId){
                $website = $this->websiteRepository->getById($websiteId);
                $this->saveProduct($_product->getSku(), $website);
            }
        }
    }

    /**
     * @param string $sku
     * @param \Magento\Store\Api\Data\WebsiteInterface $website
     * @throws LocalizedException
     */
    protected function saveProduct($sku, $website)
    {
        if (!$this->generalConfig->isEnabled($website->getCode())) {
            return;
        }

        if (!($storeId = $this->getDefaultStoreId($website->getDefaultGroupId()))) {
            $this->logger->critical(__('Default store not found for website "%1"', $website->getName()));
            return;
        }

        $product = $this->productRepository->get($sku, false, $storeId);
        if (!$product || !$this->productHelper->isSubscriptionEnabled($product)) {
            return;
        }

        try {
            $this->platformProductManager->saveProduct($product, $website->getId());
        } catch (HttpException $e) {
            $this->logger->critical($e);
            throw new LocalizedException(
                __('Fail to save product on Subscribe Pro platform for website "%1".', $website->getName())
            );
        }
    }

    /**
     * @param int $defaultGroupId
     * @return int|null
     */
    protected function getDefaultStoreId($defaultGroupId)
    {
        $group = $this->storeManager->getGroup($defaultGroupId);
        return $group ? $group->getDefaultStoreId() : null;
    }

    public function getAppState()
    {
        return $this->state;
    }
}
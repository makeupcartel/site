<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Checkoutspage v2.x.x
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


namespace Plumrocket\Checkoutspage\Block;

use Plumrocket\Checkoutspage\Model\Config\Source\Suggestion as SuggestionConfig;

class Suggestion extends \Magento\Catalog\Block\Product\AbstractProduct
{

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection 
     */
    protected $_itemCollection;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_productVisibility;

    /**
     * @var Plumrocket\Checkoutspage\Block\Page
     */
    protected $_page;

    /**
     * @var \Magento\Catalog\Model\Product\LinkFactory
     */
    protected $_productLinkFactory;

    /**
     * @var Plumrocket\Checkoutspage\Model\Config\Source\Suggestion
     */
    protected $_suggestionConfig;

    /**
     * @var \Magento\Reports\Block\Product\Viewed
     */
    protected $_viewedProduct;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_pricingHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Review\Block\View 
     */
    protected $_reviewView;

    /**
     * @param \Magento\Catalog\Block\Product\Context     $context            
     * @param \Magento\Catalog\Model\Product\LinkFactory $productLinkFactory 
     * @param \Magento\Catalog\Model\Product\Visibility  $productVisibility  
     * @param \Magento\Catalog\Model\ProductFactory      $productFactory     
     * @param \Plumrocket\Checkoutspage\Helper\Data      $helper             
     * @param SuggestionConfig                           $suggestionConfig   
     * @param \Magento\Framework\Pricing\Helper\Data    $pricingHelper
     * @param \Magento\Reports\Block\Product\Viewed      $viewedProduct 
     * @param \Magento\Review\Model\RatingFactory        $ratingFactory     
     * @param Page                                       $page               
     * @param Array                                      $data               
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\Product\LinkFactory $productLinkFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Plumrocket\Checkoutspage\Helper\Data $helper,
        SuggestionConfig $suggestionConfig,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Reports\Block\Product\Viewed $viewedProduct,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        Page $page,
        array $data = []
    ) {
        $this->_productLinkFactory = $productLinkFactory;
        $this->_productVisibility = $productVisibility;
        $this->successPage = $page;
        $this->_suggestionConfig = $suggestionConfig;
        $this->_productFactory = $productFactory;
        $this->_viewedProduct = $viewedProduct;
        $this->_pricingHelper = $pricingHelper;
        $this->_ratingFactory = $ratingFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get suggestion type
     * @return string 
     */
    public function getItemType()
    {
        $type = $this->_getSuggestionType();
        $types = $this->_suggestionConfig->toArray();
        if (isset($types[$type])) {
            return $type;
        }

        return SuggestionConfig::SUGGESTION_TYPE_CROSSSEL;
    }

    /**
     * get suggestion type from settings
     * @return string 
     */
    protected function _getSuggestionType()
    {
        return ($this->getIsEmail()) ? $this->successPage->getSettings('email/suggestion_type') : $this->successPage->getSettings('suggestion/type');
    }

    /**
     * Get method name
     * @return string
     */
    protected function _getMethodName()
    {
        $type = $this->getItemType();
        $method = 'use' . ucfirst($type) . 'Links';
        if (!method_exists($this->_getProductLinkFactory()->create(), $method)) {
            $method = 'use' . SuggestionConfig::SUGGESTION_TYPE_CROSSSEL . 'Links';
        }

        return $method;
    }

    /**
     * Create and get product factory
     * @return object
     */
    protected function _getProductLinkFactory()
    {
        if ($this->_productLinkFactory === null) {
            $this->_productLinkFactory = $this->_productLinkFactory->create();
        }
        return $this->_productLinkFactory;
    }

    /**
     * Get suggestion product collection
     * @return \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection
     */
    public function getItems()
    {
        if ($this->_itemCollection === null) {
            if ($this->showRecentlyViewedProducts()) {
                $this->_itemCollection = $this->getRecentlyViewed();
            } else {
                $this->_itemCollection = [];
                $method = $this->_getMethodName();
                foreach ($this->_getItemProductIds() as $productId) {

                    $product = $this->_productFactory->create()->load($productId);

                    $collection = $this->_productLinkFactory->create()->$method()

                    ->getProductCollection()
                    ->setStoreId(
                        $this->_storeManager->getStore()->getId()
                    )->addStoreFilter()->setVisibility(
                        $this->_productVisibility->getVisibleInCatalogIds()
                    )
                    ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
                    ->addProductFilter([$productId]);

                    $collection
                        ->addPriceData()
                        ->setPositionOrder()
                        ->load();

                    if ($collection->getSize()) {
                        foreach ($collection as $item) {
                            if (!isset($this->_itemCollection[$item->getId()]) && !in_array($item->getId(), $this->_getItemProductIds())) {
                                $this->_itemCollection[$item->getId()] = $item;
                            }
                        }
                    }
                }
                $this->_randomize();
            }

        }

        return $this->_itemCollection;
    }

    /**
     * Randomize array and cut elements
     * @return $this
     */
    protected function _randomize()
    {
        shuffle($this->_itemCollection);
        if (count($this->_itemCollection) > $this->getItemCount()) {
            array_splice($this->_itemCollection, $this->getItemCount());
        }

        return $this;
    }

    /**
     * Get order product ids
     * @return array
     */
    public function _getItemProductIds()
    {

        if ($this->getIsEmail() && $this->getOrder()) {
            $items = $this->getOrder()->getAllVisibleItems();
        } else {
            $items = $this->successPage->getOrder()->getAllVisibleItems();
        }

        if (!count($items)) {
            return [];
        }

        $productIds = [];
        foreach ($items as $item) {
            $productIds[] = (int)$item['product_id'];
        }

        return $productIds;
    }

    /**
     * is section enabled
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->successPage->getSettings('suggestion/enabled');
    }

    /**
     * Items quantity will be capped to this value
     * @return int 
     */
    public function getItemCount()
    {
        if ($this->getIsEmail()) {
            return 3;
        }

        $itemCount = (int)$this->successPage->getSettings('suggestion/number');
        if (!$itemCount) {
            $itemCount = 5;
        }

        return $itemCount;
    }

    /**
     * Is show recently viewed products
     * @return boolean 
     */
    public function showRecentlyViewedProducts()
    {
        if ($this->getIsEmail()) {
            return false;
        }

        return $this->getItemType() == SuggestionConfig::SUGGESTION_TYPE_RECENTLY_VIEWED;

    }

    /**
     * Get item collection
     * @return Magento/Catalog/Model/Producct 
     */
    public function getRecentlyViewed()
    {
        $items = $this->_viewedProduct->setPageSize($this->getItemCount())
            ->getItemsCollection()
            ->excludeProductIds($this->_getItemProductIds());

        if ($customerId = $this->successPage->getOrder()->getCustomerId()) {
            $items->setCustomerId($customerId);
        }


        return $items;
    }

    /**
     * Escape currency
     * @param  string $value
     * @return string
     */
    public function escapeCurrency($value)
    {
        return $this->_pricingHelper->currency($value);
    }

    /**
     * Get review rating
     * @param  int $productId 
     * @return \Magento\Review\Model\RatingFactory
     */
    public function getReviewsSummary($productId)
    {
        return $this->_ratingFactory->create()->getEntitySummary($productId);
    }

    /**
     * Defines a proportional height
     *
     * @param int $width
     * @param \Magento\Catalog\Block\Product\Image $image
     * @return int
     */
    public function getHeightByWidth($width, $image)
    {
        return (int)($image->getHeight() / ($image->getWidth() / $width));
    }
}

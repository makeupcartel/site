<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Convert\MiniCart\CustomerData;

/**
 * Default item
 */
class DefaultItem extends \Magento\Checkout\CustomerData\DefaultItem
{
	/**
     * string
	 */
	private $attributeCode = 'brand';

	/**
     * @var \Magento\Catalog\Model\ResourceModel\ProductFactory
	 */
	private $productFactory;

	/**
     * @var \Magento\Store\Model\StoreManagerInterface
	 */
	private $_storeManager;

	/**
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Msrp\Helper\Data $msrpHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Framework\Escaper|null $escaper
     * @param \Magento\Catalog\Model\ResourceModel\ProductFactory $productFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Msrp\Helper\Data $msrpHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\Escaper $escaper = null,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
    	$this->productFactory = $productFactory;
    	$this->_storeManager = $storeManager;
    	parent::__construct(
    		$imageHelper,
    		$msrpHelper,
    		$urlBuilder,
    		$configurationPool,
    		$checkoutHelper,
    		$escaper
    	);
    }

	/**
     * {@inheritdoc}
     */
    protected function doGetItemData()
    {
        $result = parent::doGetItemData();
        $result[$this->attributeCode] = '';
        $product = $this->item->getProduct();
        $brandId = $product->getResource()->getAttributeRawValue(
        	$product->getId(),
        	$this->attributeCode,
        	$this->_storeManager->getStore()->getId()
        );
        if($brandId){
	        $poductAttribute = $this->productFactory->create();
			$attribute = $poductAttribute->getAttribute($this->attributeCode);
			if ($attribute->usesSource()) {
			    $optionText = $attribute->getSource()->getOptionText($brandId);
			    $result[$this->attributeCode] = $optionText;
			}
        }
        
        return $result;
    }
}
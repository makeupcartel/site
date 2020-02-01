<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Convert\MiniCart\Plugin\Checkout\Model;

use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Default item
 */
class DefaultConfigProvider
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
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\ProductFactory $productFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Session $checkoutSession
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\ProductFactory $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CheckoutSession $checkoutSession
    ) {
        $this->productFactory = $productFactory;
        $this->_storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
    }
    
    public function afterGetConfig(\Magento\Checkout\Model\DefaultConfigProvider $subject, $result) {
        $items = $result['totalsData']['items'];
        $items = $this->getTotalsData($items);
        $result['totalsData']['items'] =  $items;
        return  $result;
    }
    
    /**
     * Return quote totals data
     *
     * @return array
     */
    private function getTotalsData($items)
    {
        $data = [];
        foreach(array_reverse($this->checkoutSession->getQuote()->getAllVisibleItems()) as $item){
            $product = $item->getProduct();
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
                    $data[$item->getId()] = $optionText;
                }
            }
        }
        
        foreach($items as $k => $_item){
            if(isset($data[$_item['item_id']])){
                $items[$k]['brand'] = $data[$_item['item_id']];
            }
        }
        return $items;
    }

}
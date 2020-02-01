<?php

namespace Convert\Catalog\Plugin\Block\Product\Renderer;

use Magento\ConfigurableProduct\Helper\Data;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Framework\Json\EncoderInterface;
use Magento\Swatches\Model\Swatch;

/**
 * Class Configurable
 *
 * @package Convert\Catalog\Plugin\Block\Product\Renderer
 */
class Configurable extends \Magento\Swatches\Block\Product\Renderer\Configurable
{
    /**
     * @param array $attributeData
     * @return array
     * @since 100.0.3
     */
    protected function getConfigurableOptionsIds(array $attributeData)
    {
        $ids = [];
        foreach ($this->getAllowProductIncludeOutOfStock() as $product) {
            /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $attribute */
            foreach ($this->helper->getAllowAttributes($this->getProduct()) as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                if (isset($attributeData[$productAttributeId])) {
                    $ids[$product->getData($productAttribute->getAttributeCode())] = 1;
                }
            }
        }
        return array_keys($ids);
    }
    
    /**
     * Get Allowed Products
     *
     * @return \Magento\Catalog\Model\Product[]
     */
    public function getAllowProductIncludeOutOfStock()
    {
        $products = [];
        $allProducts = $this->getProduct()->getTypeInstance()->getUsedProducts($this->getProduct(), null);
        foreach ($allProducts as $product) {
            $products[] = $product;
        }
        return $products;
    }
}
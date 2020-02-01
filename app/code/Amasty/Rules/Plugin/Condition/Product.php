<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Plugin\Condition;

class Product
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Amasty\Rules\Helper\Data
     */
    protected $rulesDataHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $productModel;
    /**
     * @var \Amasty\Rules\Model\ConfigModel
     */
    private $configModel;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Catalog\Model\Product $productModel
     * @param \Amasty\Rules\Helper\Data $rulesDataHelper
     * @param \Amasty\Rules\Model\ConfigModel $configModel
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\Product $productModel,
        \Amasty\Rules\Helper\Data $rulesDataHelper,
        \Amasty\Rules\Model\ConfigModel $configModel
    ) {
        $this->_objectManager = $objectManager;
        $this->rulesDataHelper = $rulesDataHelper;
        $this->productModel = $productModel;
        $this->configModel = $configModel;
    }

    /**
     * @param \Magento\Rule\Model\Condition\Product\AbstractProduct $subject
     * @return \Magento\Rule\Model\Condition\Product\AbstractProduct
     */
    public function afterLoadAttributeOptions(
        \Magento\Rule\Model\Condition\Product\AbstractProduct $subject
    ) {
        $attributes = [];
        $attributes['quote_item_sku'] = __('Custom Options SKU');

        if ($this->configModel->getOptionsValue()) {
            $attributes['quote_item_value'] = __('Custom Options Values');
        }

        $subject->setAttributeOption(array_merge($subject->getAttributeOption(), $attributes));
        return $subject;
    }

    /**
     * @param \Magento\Rule\Model\Condition\Product\AbstractProduct $subject
     * @param \Magento\Framework\Model\AbstractModel $object
     */
    public function beforeValidate(
        \Magento\Rule\Model\Condition\Product\AbstractProduct $subject,
        \Magento\Framework\Model\AbstractModel $object
    ) {
        if ($object->getProduct() instanceof \Magento\Catalog\Model\Product) {
            $product = $object->getProduct();
        } else {
            $product = $this->productModel->load($object->getProductId());
        }

        if ($product && $product->getTypeId() !== 'skip') {
            if ($this->configModel->getOptionsValue()) {
                $options = $product->getTypeInstance(true)->getOrderOptions($product);
                $values = '';
                if (isset($options['options'])) {
                    foreach ($options['options'] as $option) {
                        $values .= '|' . $option['value'];
                    }
                }

                $product->setQuoteItemValue($values);
            }

            $product->setQuoteItemSku($object->getSku());
            $object->setProduct($product);
        }
    }
}

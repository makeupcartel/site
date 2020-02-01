<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2016-04-20T14:03:26+00:00
 * File:          app/code/Xtento/ProductExport/Model/Export/Condition/Product/Found.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\Export\Condition\Product;

class Found extends \Magento\SalesRule\Model\Rule\Condition\Product\Found
{
    /**
     * @var \Xtento\ProductExport\Model\Export\Condition\Product
     */
    protected $conditionProduct;

    /**
     * Found constructor.
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\SalesRule\Model\Rule\Condition\Product $ruleConditionProduct
     * @param \Xtento\ProductExport\Model\Export\Condition\Product $conditionProduct
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\SalesRule\Model\Rule\Condition\Product $ruleConditionProduct,
        \Xtento\ProductExport\Model\Export\Condition\Product $conditionProduct,
        array $data = []
    ) {
        $this->conditionProduct = $conditionProduct;
        parent::__construct($context, $ruleConditionProduct, $data);
        $this->setType('Xtento\ProductExport\Model\Export\Condition\Product\Found');
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() .
            __(
                "If an item is %1 with %2 of these conditions true:",
                $this->getValueElement()->getHtml(),
                $this->getAggregatorElement()->getHtml()
            );
        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }
        return $html;
    }

    public function getNewChildSelectOptions()
    {
        $productCondition = $this->conditionProduct;
        $productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();
        $pAttributes = [];
        foreach ($productAttributes as $code => $label) {
            $pAttributes[] = [
                'value' => 'Xtento\ProductExport\Model\Export\Condition\Product|' . $code,
                'label' => $label
            ];
        }

        $conditions = [
            ['value' => '', 'label' => __('Please choose a condition to add.')],
            ['label' => __('Product Attribute'), 'value' => $pAttributes],
        ];
        return $conditions;
    }

    public function validate(\Magento\Framework\Model\AbstractModel $object)
    {
        $all = $this->getAggregator() === 'all';
        $true = (bool)$this->getValue();
        #$found = false;

        $found = $all;
        foreach ($this->getConditions() as $cond) {
            $validated = $cond->validate($object);
            if (($all && !$validated) || (!$all && $validated)) {
                $found = $validated;
            }
        }
        // if (($found && $true) || (!$true && $found)) {}
        // found an item and we're looking for existing one
        if ($found && $true) {
            return true;
        } // not found and we're making sure it doesn't exist
        elseif (!$found && !$true) {
            return true;
        }
        return false;
    }
}
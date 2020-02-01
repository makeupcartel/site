<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    \Unirgy\RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\RapidFlow\Model\Rule\Condition;

use Magento\CatalogRule\Model\Rule\Condition\Combine as ConditionCombine;
use Magento\CatalogRule\Model\Rule\Condition\ProductFactory;
use Magento\Rule\Model\Condition\Combine as ModelConditionCombine;
use Magento\Rule\Model\Condition\Context;

class Combine extends ConditionCombine
{
    /**
     * @var Product
     */
    protected $_ruleConditionProduct;

    public function __construct(
        Context $context,
        ProductFactory $conditionFactory,
        Product $ruleConditionProduct,
        array $data = []
    ) {
        parent::__construct($context, $conditionFactory, $data);
        $this->_ruleConditionProduct = $ruleConditionProduct;
        $this->setType('Unirgy\RapidFlow\Model\Rule\Condition\Combine');
    }

    public function getNewChildSelectOptions()
    {
        $productCondition = $this->_ruleConditionProduct;
        $productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();
        $attributes = [];
        foreach ($productAttributes as $code => $label) {
            $attributes[] = ['value' => 'Unirgy\RapidFlow\Model\Rule\Condition\Product|' . $code, 'label' => $label];
        }
        $conditions = ModelConditionCombine::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, array(
            ['value' => 'Unirgy\RapidFlow\Model\Rule\Condition\Combine', 'label' => __('Conditions Combination')],
            ['label' => __('Product Attribute'), 'value' => $attributes],
        ));
        return $conditions;
    }

    public function asSqlWhere($collection)
    {
        $w = [];
        foreach ($this->getConditions() as $cond) {
            $w[] = $cond->asSqlWhere($collection);
        }
        if (!$w) {
            return false;
        }
        $a = $this->getAggregator();
        $v = $this->getValue();
        return ($v ? '' : 'NOT ') . '(' . join(') ' . ($a == 'all' ? 'AND' : 'OR') . ' ' . ($v ? '' : 'NOT ') . '(',
                                               $w) . ')';
    }
}

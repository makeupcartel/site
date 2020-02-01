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

use Magento\Backend\Helper\Data as HelperData;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product as ResourceModelProduct;
use Magento\CatalogRule\Model\Rule\Condition\Product as ConditionProduct;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
use Magento\Framework\Locale\FormatInterface;
use Magento\Rule\Model\Condition\Context;
use Unirgy\RapidFlow\Helper\Data as RfData;
use Unirgy\RapidFlow\Model\ResourceModel\Catalog\Product as RfProduct;
use Unirgy\RapidFlow\Model\ResourceModel\Catalog\Product\Collection as ProductCollection;

/**
 * Class Product
 * @method string getAttribute
 * @package Unirgy\RapidFlow\Model\Rule\Condition
 */
class Product extends ConditionProduct
{
    /**
     * @var Type
     */
    protected $_modelProductType;

    /**
     * @var Resource
     */
    protected $_frameworkModelResource;

    /**
     * @var RfData
     */
    private $rfHelper;

    /**
     * Product constructor.
     * @param Context $context
     * @param HelperData $backendData
     * @param Config $config
     * @param ProductFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param ResourceModelProduct $productResource
     * @param Collection $attrSetCollection
     * @param FormatInterface $localeFormat
     * @param RfData $dataHelper
     * @param array $data
     * @param Type|null $modelProductType
     */
    public function __construct(
        Context $context,
        HelperData $backendData,
        Config $config,
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        ResourceModelProduct $productResource,
        Collection $attrSetCollection,
        FormatInterface $localeFormat,
        RfData $dataHelper,
        Type $modelProductType,
        array $data = []
    ) {
        $this->_modelProductType = $modelProductType;

        parent::__construct($context, $backendData, $config, $productFactory, $productRepository, $productResource,
                            $attrSetCollection, $localeFormat, $data);
        $this->setType('urapidflow/rule_condition_product');
        $this->rfHelper = $dataHelper;
    }

    protected function _addSpecialAttributes(array &$attributes)
    {
        parent::_addSpecialAttributes($attributes);
        $attributes['type_id'] = __('Product Type (system)');
        $attributes['created_at'] = __('Created At (system)');
        $attributes['updated_at'] = __('Updated At (system)');
        $attributes['entity_id'] = __('Product Id (system)');
    }

    public function getJsFormObject()
    {
        return 'rule_conditions_fieldset';
    }

    public function loadOperatorOptions()
    {
        parent::loadOperatorOptions();
        $byInputType = $this->getOperatorByInputType();
        $byInputType['multiselect'] = ['==', '!=', '()', '!()'];
        $this->setOperatorByInputType($byInputType);
        return $this;
    }

    public function loadAttributeOptions()
    {
        $productAttributes = $this->_productResource
            ->loadAllAttributes()
            ->getAttributesByCode();

        $attributes = [];
        foreach ($productAttributes as $attribute) {
#var_dump($attribute->debug());
            if ($attribute->getFrontendLabel() != '' && ($attribute->getIsVisible() || $attribute->getIsUsedForPromoRules())) {
                $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
            }
        }

        $this->_addSpecialAttributes($attributes);

        asort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    public function getInputType()
    {
        $attributeCode = $this->getAttribute();
        if ($attributeCode === 'type_id') {
            return 'multiselect';
        } else if ($attributeCode === 'created_at' || $attributeCode === 'updated_at') {
            return 'date';
        }
        return parent::getInputType();
    }

    public function getValueElementType()
    {
//        if ($this->getAttribute() === 'type_id') {
//            return 'multiselect';
//        }
        $attributeCode = $this->getAttribute();
        if ($attributeCode === 'type_id') {
            return 'multiselect';
        } else if ($attributeCode === 'created_at' || $attributeCode === 'updated_at') {
            return 'date';
        }
        return parent::getValueElementType();
    }

    public function getValueSelectOptions()
    {
        if ($this->getAttribute() === 'type_id') {
            $arr = $this->_modelProductType->getOptionArray();
            $options = [];
            foreach ($arr as $k => $v) {
                $options[] = ['value' => $k, 'label' => $v];
            }
            return $options;
        }
        return parent::getValueSelectOptions();
    }

    /**
     * @param ProductCollection $collection
     * @return bool|mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function asSqlWhere($collection)
    {
        $a = $where = $this->getAttribute();
        $o = $this->getOperator();
        $v = $this->getValue();
        if (is_array($v)) {
            $ve = addslashes(join(',', $v));
        } else {
            $ve = addslashes($v);
        }

        if ($a === 'category_ids') {
            $entityId = 'entity_id';
            if ($this->rfHelper->hasMageFeature(RfProduct::ROW_ID)) {
                $entityId = RfProduct::ROW_ID;
            }
            $res = $this->_productResource;
            $read = $res->getConnection();
            $sql = $read->quoteInto("SELECT product_id FROM `{$res->getTable('catalog_category_product')}` WHERE category_id IN (?)",
                                    explode(',', $v));
            switch ($o) {
                case '==':
                case '()':
                    $w = "e.{$entityId} in ({$sql})";
                    break;

                case '!=':
                case '!()':
                    $w = "e.{$entityId} not in ({$sql})";
                    break;

                default:
                    return false;
            }
        } else {
            $attr = $this->_productResource->getAttribute($a);

            if ($attr->getId() && $attr->getBackendType() === 'datetime') {
                if (!is_int($ve)) {
                    $timestamp = strtotime($ve);
                } else {
                    $timestamp = $ve;
                }
                $ve = date('Y-m-d H:i:s', $timestamp);
            }

            // whether attribute is multivalue
            $m = $attr->getId() && ($attr->getFrontendInput() === 'multiselect');

            switch ($o) {
                case '==':
                case '!=':
                    $wt = '{{ta}}' . ($o === '==' ? '=' : '<>') . "'{$ve}'";
                    break;

                case '>=':
                case '<=':
                case '>':
                case '<':
                    $wt = "{{ta}}{$o}'{$ve}'";
                    break;

                case '{}':
                case '!{}':
                    $wt = '{{ta}} ' . ($o === '!{}' ? 'NOT ' : '') . "LIKE '%{$ve}%'";
                    break;

                case '()':
                case '!()':
                    $va = preg_split('|\s*,\s*|', $ve);
                    if (!$m) {
                        $wt = '{{ta}} ' . ($o === '!()' ? 'NOT ' : '') . "IN ('" . join("','", $va) . "')";
                    } else {
                        $w1 = [];
                        foreach ($va as $v1) {
                            $w1[] = "find_in_set('" . addslashes($v1) . "', {{ta}})";
                        }
                        $wt = '(' . join(') OR (', $w1) . ')';
                    }
                    break;

                default:
                    return false;
            }

            if ($attr->getId() && $attr->getBackendType() !== 'static') {
                $collection->addAttributeToJoin($a);
                $sql = $collection->getSelect();
                $attrTable = $collection->getAttributeTableAlias($a);
                $dt = strpos($sql, "`{$attrTable}_default`") !== false;
                $dw = str_replace('{{ta}}', "{$attrTable}_default.value", $wt);
                $st = strpos($sql, "`{$attrTable}`") !== false;
                $sw = str_replace('{{ta}}', "{$attrTable}.value", $wt);
                if ($dt && $st) {
                    $w = "ifnull({$sw}, {$dw})";
                } elseif ($dt && !$st) {
                    $w = $dw;
                } else {
                    $w = $sw;
                }
            } else {
                $w = str_replace('{{ta}}', $a, $wt);
            }
        }

        return $w;
    }
}

<?php

namespace Unirgy\RapidFlow\Block\Adminhtml\Profile\Edit\Tab;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Eav\Model\Config;
use Unirgy\RapidFlow\Helper\Data as HelperData;
use Unirgy\RapidFlow\Model\Profile;
use Unirgy\RapidFlow\Model\ResourceModel\Catalog\Product as ProductResource;

/**
 * Class Columns
 *
 * @method Profile getProfile()
 * @package Unirgy\RapidFlow\Block\Adminhtml\Profile\Edit\Tab
 */
class Columns
    extends Template
{
    /**
     * @var Config
     */
    protected $_eavConfig;

    /**
     * @var HelperData
     */
    protected $_rapidFlowHelper;

    /**
     * @var ProductResource
     */
    protected $_productResource;

    public function __construct(
        Context $context,
        Config $eavConfig,
        HelperData $rapidFlowHelper,
        ProductResource $productResource,
        array $data = []
    ) {
        $this->_eavConfig = $eavConfig;
        $this->_rapidFlowHelper = $rapidFlowHelper;
        $this->_productResource = $productResource;

        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Unirgy_RapidFlow::urapidflow/columns.phtml');
    }

    public function getColumnsFields()
    {
        $groups = [];

        $entityType = $this->_eavConfig->getEntityType('catalog_product');
        $attrs = $entityType->getAttributeCollection();
        $fields = [];
        $hidden = [];
        $removeFields = ['has_options', 'required_options', 'category_ids', 'minimal_price'];
        if ($this->getProfile()->getProfileType() === 'import') {
            $removeFields = array_merge($removeFields, ['created_at', 'updated_at']);
        }
        /** @var \Magento\Eav\Model\Entity\Attribute $a */
        foreach ($attrs as $k => $a) {
            $attr = $a->toArray();
            if ($attr['frontend_input'] === 'gallery' || in_array($attr['attribute_code'], $removeFields)) {
                continue;
            }
            if (empty($attr['frontend_label'])) {
                $attr['frontend_label'] = $attr['attribute_code'];
            }
            if (in_array($attr['frontend_input'], ['select', 'multiselect'])) {
                try {
                    if (!$a->getSource()) {
                        continue;
                    }
                    $opts = $a->getSource()->getAllOptions();
                    foreach ($opts as $o) {
                        if (is_array($o['value'])) {
                            foreach ($o['value'] as $o1) {
                                $attr['options'][$o['label']][$o1['value']] = $o1['label'];
                            }
                        } elseif (is_scalar($o['value'])) {
                            $attr['options'][$o['value']] = $o['label'];
                        }
                    }
                } catch (\Exception $e) {
                    // can be all kinds of custom source models, just ignore
                    $this->_logger->warning($e->getMessage());
                }
            }
            if (!empty($attr['is_visible'])) {
                $fields[$attr['attribute_code']] = $attr;
            } else {
                unset($attr['is_required']);
                $hidden[$attr['attribute_code']] = $attr;
            }
        }
        $groups['attributes'] = ['label' => __('Product Attributes'), 'fields' => $fields];
        $groups['hidden'] = ['label' => __('Hidden Attributes'), 'fields' => $hidden];
        if ($this->getProfile()->getProfileType() === 'export') {
            $groups['price'] = [
                'label' => __('Price'),
                'fields' => [
                    'price.final' => [
                        'attribute_code' => 'price.final',
                        'frontend_input' => 'text',
                        'frontend_label' => __('Final Price'),
                        'backend_type' => 'decimal'
                    ],
                    'price.minimal' => [
                        'attribute_code' => 'price.minimal',
                        'frontend_input' => 'text',
                        'frontend_label' => __('Minimal Price'),
                        'backend_type' => 'decimal'
                    ]
                ]
            ];
            $groups['price']['fields']['price.maximum'] = [
                'attribute_code' => 'price.maximum',
                'frontend_input' => 'text',
                'frontend_label' => __('Maximum Price'),
                'backend_type' => 'decimal'
            ];
        }

        $attrs = $this->_productResource->fetchSystemAttributes();
        $gr = [
            'product' => __('System Attributes'),
            'stock' => __('Inventory Stock'),
            'category' => __('Category'),
        ];
        if ($this->getProfile()->getProfileType() === 'import') {
            $removeFields = array_merge($removeFields,
                                        ['product.entity_id', 'price.final', 'price.minimal', 'price.maximum']);
        }

        foreach ($attrs as $f => $a) {
            if (in_array($f, $removeFields)) continue;
            $fa = explode('.', $f, 2);
            if (empty($fa[1])) {
                if (strpos($f, '_type') !== false) {
                    if (empty($a['frontend_label'])) {
                        $a['frontend_label'] = $f;
                    }
                    $groups['hidden']['fields'][$f] = $a;
                }
                continue;
            }
            if (empty($groups[$fa[0]])) {
                $groups[$fa[0]] = ['label' => $gr[$fa[0]], 'fields' => []];
            }
            $a['attribute_code'] = $f;
            $groups[$fa[0]]['fields'][$f] = $a;
        }
        $groups['const'] = [
            'label' => __('Constant'),
            'fields' => [
                'const.value' => [
                    'attribute_code' => 'const.value',
                    'frontend_input' => 'textarea',
                    'frontend_label' => $this->getProfile()->getProfileType() === 'export' ? __('Constant Value') : __('Ignore Column'),
                ],
                'const.function' => [
                    'attribute_code' => 'const.function',
                    'frontend_input' => 'text',
                    'frontend_label' => $this->getProfile()->getProfileType() === 'export' ? __('Custom Function') : __('Ignore Column'),
                ],
            ]
        ];

        $fields = [
            'attribute_code' => 1,
            'backend_type' => 1,
            'frontend_label' => 1,
            'frontend_input' => 1,
            'options' => 1,
            'is_required' => 1
        ];

        foreach ($groups as $i => &$g) {
            uasort($g['fields'], [$this, 'sortFields']);
            foreach ($g['fields'] as $j => &$a) {
                foreach ($a as $f => $v) {
                    if (empty($fields[$f])) {
                        unset($a[$f]);
                    }
                }
                if (!empty($a['options'])) {
                    $options = $a['frontend_input'] === 'multiselect' ? [] : ['' => ''];
                    foreach ($a['options'] as $k => $v) {
                        if ($k === '') {
                            continue;
                        }
                        if (is_array($v)) {
                            foreach ($v as $k1 => $v1) {
                                $options[$k][$k1 . ' '] = $v1;
                            }
                        } else {
                            $options[$k . ' '] = $v;
                        }
                    }
                    $a['options'] = $options;
                }
            }
            unset($a);
        }
        unset($g);

        return $groups;
    }

    public function sortFields($a, $b)
    {
        return $a['frontend_label'] < $b['frontend_label'] ? -1 : ($a['frontend_label'] > $b['frontend_label'] ? 1 : 0);
    }

    public function getColumns()
    {
        return (array)$this->getProfile()->getColumns();
    }
}

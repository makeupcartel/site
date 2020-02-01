<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */


namespace Amasty\Ogrid\Plugin\Ui\Model;

class AbstractReader
{
    /**
     * Added settings for order_columns
     *
     * @return array
     */
    protected function addColumnsSettings()
    {
        $result = [
            'name' => 'config',
            'xsi:type' => 'array',
            'item' => [
                'childDefaults' => [
                    'name' => 'childDefaults',
                    'xsi:type' => 'array',
                    'item' => [
                        'storageConfig' => [
                            'name' => 'storageConfig',
                            'xsi:type' => 'array',
                            'item' => [
                                'provider' => [
                                    'name' => 'provider',
                                    'xsi:type' => 'string',
                                    'value' => 'ns = ${ $.ns }, index = bookmarks',
                                ],
                                'root' => [
                                    'name' => 'root',
                                    'xsi:type' => 'string',
                                    'value' => 'columns.${ $.index }',
                                ],
                                'namespace' => [
                                    'name' => 'namespace',
                                    'xsi:type' => 'string',
                                    'value' => 'current.${ $.storageConfig.root }',
                                ],
                            ],
                        ],
                        'fieldAction' => [
                            'name' => 'fieldAction',
                            'xsi:type' => 'array',
                            'item' => [
                                'provider' => [
                                    'name' => 'provider',
                                    'xsi:type' => 'string',
                                    'value' => 'sales_order_grid.sales_order_grid.sales_order_columns.actions',
                                ],
                                'target' => [
                                    'name' => 'target',
                                    'xsi:type' => 'string',
                                    'value' => 'applyAction',
                                ],
                                'params' => [
                                    'name' => 'params',
                                    'xsi:type' => 'array',
                                    'item' => [
                                        0 => [
                                            'name' => '0',
                                            'xsi:type' => 'string',
                                            'value' => 'view',
                                        ],
                                        1 => [
                                            'name' => '1',
                                            'xsi:type' => 'string',
                                            'value' => '${ $.$data.rowIndex }',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'component' => [
                    'name' => 'component',
                    'xsi:type' => 'string',
                    'value' => 'Magento_Ui/js/grid/listing',
                ],
                'storageConfig' => [
                    'name' => 'storageConfig',
                    'xsi:type' => 'array',
                    'item' => [
                        'namespace' => [
                            'name' => 'namespace',
                            'xsi:type' => 'string',
                            'value' => 'current',
                        ],
                        'provider' => [
                            'name' => 'provider',
                            'xsi:type' => 'string',
                            'value' => 'ns = ${ $.ns }, index = bookmarks',
                        ],
                    ],
                ],
                'componentType' => [
                    'name' => 'componentType',
                    'xsi:type' => 'string',
                    'value' => 'columns',
                ]
            ]
        ];

        return $result;
    }

    /**
     * Added settings for listing_top
     *
     * @return array
     */
    protected function addListingToolbarSettings()
    {
        $result = [
            'name' => 'config',
            'xsi:type' => 'array',
            'item' => [
                'component' => [
                    'name' => 'component',
                    'xsi:type' => 'string',
                    'value' => 'Amasty_Ogrid/js/grid/controls/columns',
                ],
                'tabsData' => [
                    'name' => 'tabsData',
                    'xsi:type' => 'array',
                    'item' => [
                        'general' => [
                            'name' => 'general',
                            'xsi:type' => 'string',
                            'value' => (string)__('General'),
                        ],
                        'product' => [
                            'name' => 'product',
                            'xsi:type' => 'string',
                            'value' => (string)__('Product'),
                        ],
                        'customer' => [
                            'name' => 'customer',
                            'xsi:type' => 'string',
                            'value' => (string)__('Customer'),
                        ],
                        'billing' => [
                            'name' => 'billing',
                            'xsi:type' => 'string',
                            'value' => (string)__('Billing'),
                        ],
                        'shipping' => [
                            'name' => 'shipping',
                            'xsi:type' => 'string',
                            'value' => (string)__('Shipping'),
                        ],
                        'unassigned' => [
                            'name' => 'unassigned',
                            'xsi:type' => 'string',
                            'value' => (string)__('Other'),
                        ],
                    ],
                ],
                'productColsData' => [
                    'name' => 'productColsData',
                    'xsi:type' => 'array',
                    'item' => [
                        'amasty_ogrid_product_name' => [
                            'name' => 'amasty_ogrid_product_name',
                            'xsi:type' => 'array',
                            'item' => [
                                'visible' => [
                                    'name' => 'visible',
                                    'xsi:type' => 'boolean',
                                    'value' => 'false',
                                ],
                                'filter' => [
                                    'name' => 'filter',
                                    'xsi:type' => 'string',
                                    'value' => 'text',
                                ],
                                'label' => [
                                    'name' => 'label',
                                    'xsi:type' => 'string',
                                    'value' => (string)__('Name'),
                                ],
                            ],
                        ],
                        'amasty_ogrid_product_sku' => [
                            'name' => 'amasty_ogrid_product_sku',
                            'xsi:type' => 'array',
                            'item' => [
                                'visible' => [
                                    'name' => 'visible',
                                    'xsi:type' => 'boolean',
                                    'value' => 'false',
                                ],
                                'filter' => [
                                    'name' => 'filter',
                                    'xsi:type' => 'string',
                                    'value' => 'text',
                                ],
                                'label' => [
                                    'name' => 'label',
                                    'xsi:type' => 'string',
                                    'value' => (string)__('SKU'),
                                ],
                            ],
                        ],
                        'amasty_ogrid_product_product_type' => [
                            'name' => 'amasty_ogrid_product_product_type',
                            'xsi:type' => 'array',
                            'item' => [
                                'visible' => [
                                    'name' => 'visible',
                                    'xsi:type' => 'boolean',
                                    'value' => 'false',
                                ],
                                'filter' => [
                                    'name' => 'filter',
                                    'xsi:type' => 'string',
                                    'value' => 'text',
                                ],
                                'label' => [
                                    'name' => 'label',
                                    'xsi:type' => 'string',
                                    'value' => (string)__('Type'),
                                ],
                            ],
                        ],
                        'amasty_ogrid_product_product_options' => [
                            'name' => 'amasty_ogrid_product_product_options',
                            'xsi:type' => 'array',
                            'item' => [
                                'visible' => [
                                    'name' => 'visible',
                                    'xsi:type' => 'boolean',
                                    'value' => 'false',
                                ],
                                'filter' => [
                                    'name' => 'filter',
                                    'xsi:type' => 'string',
                                ],
                                'label' => [
                                    'name' => 'label',
                                    'xsi:type' => 'string',
                                    'value' => (string)__('Options'),
                                ],
                            ],
                        ],
                        'amasty_ogrid_product_base_price' => [
                            'name' => 'amasty_ogrid_product_base_price',
                            'xsi:type' => 'array',
                            'item' => [
                                'visible' => [
                                    'name' => 'visible',
                                    'xsi:type' => 'boolean',
                                    'value' => 'false',
                                ],
                                'filter' => [
                                    'name' => 'filter',
                                    'xsi:type' => 'string',
                                    'value' => 'textRange',
                                ],
                                'label' => [
                                    'name' => 'label',
                                    'xsi:type' => 'string',
                                    'value' => (string)__('Price (Base)'),
                                ],
                            ],
                        ],
                        'amasty_ogrid_product_price' => [
                            'name' => 'amasty_ogrid_product_price',
                            'xsi:type' => 'array',
                            'item' => [
                                'visible' => [
                                    'name' => 'visible',
                                    'xsi:type' => 'boolean',
                                    'value' => 'false',
                                ],
                                'filter' => [
                                    'name' => 'filter',
                                    'xsi:type' => 'string',
                                    'value' => 'textRange',
                                ],
                                'label' => [
                                    'name' => 'label',
                                    'xsi:type' => 'string',
                                    'value' => (string)__('Price (Purchased)'),
                                ],
                            ],
                        ],
                        'amasty_ogrid_product_tax_percent' => [
                            'name' => 'amasty_ogrid_product_tax_percent',
                            'xsi:type' => 'array',
                            'item' => [
                                'visible' => [
                                    'name' => 'visible',
                                    'xsi:type' => 'boolean',
                                    'value' => 'false',
                                ],
                                'filter' => [
                                    'name' => 'filter',
                                    'xsi:type' => 'string',
                                    'value' => 'textRange',
                                ],
                                'label' => [
                                    'name' => 'label',
                                    'xsi:type' => 'string',
                                    'value' => (string)__('Tax Percent'),
                                ],
                            ],
                        ],
                        'amasty_ogrid_product_base_tax_amount' => [
                            'name' => 'amasty_ogrid_product_base_tax_amount',
                            'xsi:type' => 'array',
                            'item' => [
                                'visible' => [
                                    'name' => 'visible',
                                    'xsi:type' => 'boolean',
                                    'value' => 'false',
                                ],
                                'filter' => [
                                    'name' => 'filter',
                                    'xsi:type' => 'string',
                                    'value' => 'textRange',
                                ],
                                'label' => [
                                    'name' => 'label',
                                    'xsi:type' => 'string',
                                    'value' => (string)__('Tax Amount (Base)'),
                                ],
                            ],
                        ],
                        'amasty_ogrid_product_tax_amount' => [
                            'name' => 'amasty_ogrid_product_tax_amount',
                            'xsi:type' => 'array',
                            'item' => [
                                'visible' => [
                                    'name' => 'visible',
                                    'xsi:type' => 'boolean',
                                    'value' => 'false',
                                ],
                                'filter' => [
                                    'name' => 'filter',
                                    'xsi:type' => 'string',
                                    'value' => 'textRange',
                                ],
                                'label' => [
                                    'name' => 'label',
                                    'xsi:type' => 'string',
                                    'value' => (string)__('Tax Amount (Purchased)'),
                                ],
                            ],
                        ],
                        'amasty_ogrid_product_discount_percent' => [
                            'name' => 'amasty_ogrid_product_discount_percent',
                            'xsi:type' => 'array',
                            'item' => [
                                'visible' => [
                                    'name' => 'visible',
                                    'xsi:type' => 'boolean',
                                    'value' => 'false',
                                ],
                                'filter' => [
                                    'name' => 'filter',
                                    'xsi:type' => 'string',
                                    'value' => 'textRange',
                                ],
                                'label' => [
                                    'name' => 'label',
                                    'xsi:type' => 'string',
                                    'value' => (string)__('Discount Percent'),
                                ],
                            ],
                        ],
                        'amasty_ogrid_product_base_discount_amount' => [
                            'name' => 'amasty_ogrid_product_base_discount_amount',
                            'xsi:type' => 'array',
                            'item' => [
                                'visible' => [
                                    'name' => 'visible',
                                    'xsi:type' => 'boolean',
                                    'value' => 'false',
                                ],
                                'filter' => [
                                    'name' => 'filter',
                                    'xsi:type' => 'string',
                                    'value' => 'textRange',
                                ],
                                'label' => [
                                    'name' => 'label',
                                    'xsi:type' => 'string',
                                    'value' => (string)__('Discount Amount (Base)'),
                                ],
                            ],
                        ],
                        'amasty_ogrid_product_discount_amount' => [
                            'name' => 'amasty_ogrid_product_discount_amount',
                            'xsi:type' => 'array',
                            'item' => [
                                'visible' => [
                                    'name' => 'visible',
                                    'xsi:type' => 'boolean',
                                    'value' => 'false',
                                ],
                                'filter' => [
                                    'name' => 'filter',
                                    'xsi:type' => 'string',
                                    'value' => 'textRange',
                                ],
                                'label' => [
                                    'name' => 'label',
                                    'xsi:type' => 'string',
                                    'value' => (string)__('Discount Amount (Purchased)'),
                                ],
                            ],
                        ],
                        'amasty_ogrid_product_qty_ordered' => [
                            'name' => 'amasty_ogrid_product_qty_ordered',
                            'xsi:type' => 'array',
                            'item' => [
                                'visible' => [
                                    'name' => 'visible',
                                    'xsi:type' => 'boolean',
                                    'value' => 'false',
                                ],
                                'filter' => [
                                    'name' => 'filter',
                                    'xsi:type' => 'string',
                                    'value' => 'textRange',
                                ],
                                'label' => [
                                    'name' => 'label',
                                    'xsi:type' => 'string',
                                    'value' => (string)__('Qty Ordered'),
                                ],
                            ],
                        ],
                        'amasty_ogrid_product_qty_invoiced' => [
                            'name' => 'amasty_ogrid_product_qty_invoiced',
                            'xsi:type' => 'array',
                            'item' => [
                                'visible' => [
                                    'name' => 'visible',
                                    'xsi:type' => 'boolean',
                                    'value' => 'false',
                                ],
                                'filter' => [
                                    'name' => 'filter',
                                    'xsi:type' => 'string',
                                    'value' => 'textRange',
                                ],
                                'label' => [
                                    'name' => 'label',
                                    'xsi:type' => 'string',
                                    'value' => (string)__('Qty Invoiced'),
                                ],
                            ],
                        ],
                        'amasty_ogrid_product_qty_shipped' => [
                            'name' => 'amasty_ogrid_product_qty_shipped',
                            'xsi:type' => 'array',
                            'item' => [
                                'visible' => [
                                    'name' => 'visible',
                                    'xsi:type' => 'boolean',
                                    'value' => 'false',
                                ],
                                'filter' => [
                                    'name' => 'filter',
                                    'xsi:type' => 'string',
                                    'value' => 'textRange',
                                ],
                                'label' => [
                                    'name' => 'label',
                                    'xsi:type' => 'string',
                                    'value' => (string)__('Qty Shipped'),
                                ],
                            ],
                        ],
                        'amasty_ogrid_product_qty_refunded' => [
                            'name' => 'amasty_ogrid_product_qty_refunded',
                            'xsi:type' => 'array',
                            'item' => [
                                'visible' => [
                                    'name' => 'visible',
                                    'xsi:type' => 'boolean',
                                    'value' => 'false',
                                ],
                                'filter' => [
                                    'name' => 'filter',
                                    'xsi:type' => 'string',
                                    'value' => 'textRange',
                                ],
                                'label' => [
                                    'name' => 'label',
                                    'xsi:type' => 'string',
                                    'value' => (string)__('Qty Refunded'),
                                ],
                            ],
                        ],
                        'amasty_ogrid_product_qty_canceled' => [
                            'name' => 'amasty_ogrid_product_qty_canceled',
                            'xsi:type' => 'array',
                            'item' => [
                                'visible' => [
                                    'name' => 'visible',
                                    'xsi:type' => 'boolean',
                                    'value' => 'false',
                                ],
                                'filter' => [
                                    'name' => 'filter',
                                    'xsi:type' => 'string',
                                    'value' => 'textRange',
                                ],
                                'label' => [
                                    'name' => 'label',
                                    'xsi:type' => 'string',
                                    'value' => (string)__('Qty Canceled'),
                                ],
                            ],
                        ],
                        'amasty_ogrid_product_qty_available' => [
                            'name' => 'amasty_ogrid_product_qty_available',
                            'xsi:type' => 'array',
                            'item' => [
                                'visible' => [
                                    'name' => 'visible',
                                    'xsi:type' => 'boolean',
                                    'value' => 'false',
                                ],
                                'filter' => [
                                    'name' => 'filter',
                                    'xsi:type' => 'string',
                                    'value' => 'textRange',
                                ],
                                'label' => [
                                    'name' => 'label',
                                    'xsi:type' => 'string',
                                    'value' => (string)__('Qty Available'),
                                ],
                            ],
                        ],
                        'amasty_ogrid_product_base_row_total' => [
                            'name' => 'amasty_ogrid_product_base_row_total',
                            'xsi:type' => 'array',
                            'item' => [
                                'visible' => [
                                    'name' => 'visible',
                                    'xsi:type' => 'boolean',
                                    'value' => 'false',
                                ],
                                'filter' => [
                                    'name' => 'filter',
                                    'xsi:type' => 'string',
                                    'value' => 'textRange',
                                ],
                                'label' => [
                                    'name' => 'label',
                                    'xsi:type' => 'string',
                                    'value' => (string)__('Row Total (Base)'),
                                ],
                            ],
                        ],
                        'amasty_ogrid_product_row_total' => [
                            'name' => 'amasty_ogrid_product_row_total',
                            'xsi:type' => 'array',
                            'item' => [
                                'visible' => [
                                    'name' => 'visible',
                                    'xsi:type' => 'boolean',
                                    'value' => 'false',
                                ],
                                'filter' => [
                                    'name' => 'filter',
                                    'xsi:type' => 'string',
                                    'value' => 'textRange',
                                ],
                                'label' => [
                                    'name' => 'label',
                                    'xsi:type' => 'string',
                                    'value' => (string)__('Row Total (Purchased)'),
                                ],
                            ],
                        ],
                    ],
                ],
                'storageConfig' => [
                    'name' => 'storageConfig',
                    'xsi:type' => 'array',
                    'item' => [
                        'provider' => [
                            'name' => 'provider',
                            'xsi:type' => 'string',
                            'value' => 'sales_order_grid.sales_order_grid.listing_top.bookmarks',
                        ],
                        'namespace' => [
                            'name' => 'namespace',
                            'xsi:type' => 'string',
                            'value' => 'current',
                        ],
                    ],
                ],
                'clientConfig' => [
                    'name' => 'clientConfig',
                    'xsi:type' => 'array',
                    'item' => [
                        'saveUrl' => [
                            'name' => 'saveUrl',
                            'xsi:type' => 'url',
                            'path' => 'amasty_ogrid/index/bookmarks',
                        ],
                        'validateBeforeSave' => [
                            'name' => 'validateBeforeSave',
                            'xsi:type' => 'boolean',
                            'value' => 'false',
                        ]
                    ]
                ]
            ]
        ];

        return $result;
    }

    /**
     * Added settings for order grid
     *
     * @param array $result
     *
     * @return array
     */
    protected function addAmastySettings($result)
    {
        if (isset($result['listing_top']['children']['columns_controls']['arguments']['data']['item']['config'])) {
            $result['listing_top']['children']['columns_controls']['arguments']['data']['item']['config'] =
                $this->addListingToolbarSettings();

            if (isset($result['sales_order_columns']['arguments']['data']['item']['config'])) {
                $result['sales_order_columns']['arguments']['data']['item']['config'] = $this->addColumnsSettings();
            }

            if (isset($result['sales_order_columns']['attributes'])) {
                $result['sales_order_columns']['attributes'] = [
                    'class' => 'Amasty\Ogrid\Ui\Component\Listing\Columns',
                    'component' => 'Magento_Ui/js/grid/listing',
                    'name' => 'sales_order_columns'
                ];
            }

            if (isset($result['listing_top']['children']['columns_controls'])) {
                $result['listing_top']['children']['columns_controls']['attributes'] = [
                    'class' => 'Amasty\Ogrid\Ui\Component\Columns',
                    'component' => 'Magento_Ui/js/grid/controls/columns',
                    'name' => 'columns_controls'
                ];
            }

            if (isset($result['listing_top']['children']['export_button'])) {
                $result['listing_top']['children']['export_button']['attributes']['class'] =
                    'Amasty\Ogrid\Ui\Component\ExportButton';
            }
        }

        return $result;
    }
}

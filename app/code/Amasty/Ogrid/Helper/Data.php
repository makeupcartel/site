<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */

namespace Amasty\Ogrid\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SCOPE_GENERAL_HIDE_STATUSES = 'amasty_ogrid/general/hide_statuses';

    protected $_indexerHandlerFactory;
    protected $_attributeCollectionFactory;
    protected $_attributeCollection;
    protected $_scopeConfig;
    protected $_hideStatuses;

    protected $_orderFields = [
        'amasty_ogrid_shipping_description' => 'Order\\ShippingMethod',
        'amasty_ogrid_base_subtotal' => 'Order\\BaseSubtotal',
        'amasty_ogrid_total_due' => 'Order\\TotalDue',
        'amasty_ogrid_total_paid' => 'Order\\TotalPaid',
        'amasty_ogrid_subtotal' => 'Order\\Subtotal',
        'amasty_ogrid_coupon_code' => 'Order\\CouponCode',
        'amasty_ogrid_weight' => 'Order\\Weight',
        'amasty_ogrid_tax_amount' => 'Order\\OrderTax',
        'amasty_ogrid_order_comments' => 'Order\\Comments',
        'amasty_ogrid_items_sku' => 'Order\\Skus',

        'amasty_ogrid_shipping_fax' => 'Address\\Shipping\\Fax',
        'amasty_ogrid_shipping_region' => 'Address\\Shipping\\Region',
        'amasty_ogrid_shipping_postcode' => 'Address\\Shipping\\Postcode',
        'amasty_ogrid_shipping_city' => 'Address\\Shipping\\City',
        'amasty_ogrid_shipping_company' => 'Address\\Shipping\\Company',
        'amasty_ogrid_shipping_telephone' => 'Address\\Shipping\\Telephone',
        'amasty_ogrid_shipping_country_id' => 'Address\\Shipping\\CountryId',
        'amasty_ogrid_billing_fax' => 'Address\\Billing\\Fax',
        'amasty_ogrid_billing_region' => 'Address\\Billing\\Region',
        'amasty_ogrid_billing_postcode' => 'Address\\Billing\\Postcode',
        'amasty_ogrid_billing_city' => 'Address\\Billing\\City',
        'amasty_ogrid_billing_company' => 'Address\\Billing\\Company',
        'amasty_ogrid_billing_telephone' => 'Address\\Billing\\Telephone',
        'amasty_ogrid_billing_country_id' => 'Address\\Billing\\CountryId',
        'amasty_ogrid_customer_group_code' => 'Customer\\Group',

    ];

    protected $_orderItemFields = [
        'amasty_ogrid_product_name' => 'Product\\Name',
        'amasty_ogrid_product_sku' => 'Product\\Sku',
        'amasty_ogrid_product_product_type' => 'Product\\Type',
        'amasty_ogrid_product_product_options' => 'Product\\Option',
        'amasty_ogrid_product_price' => 'Product\\Price',
        'amasty_ogrid_product_base_price' => 'Product\\BasePrice',
        'amasty_ogrid_product_tax_percent' => 'Product\\TaxPercent',
        'amasty_ogrid_product_tax_amount' => 'Product\\TaxAmount',
        'amasty_ogrid_product_base_tax_amount' => 'Product\\BaseTaxAmount',
        'amasty_ogrid_product_discount_percent' => 'Product\\DiscountPercent',
        'amasty_ogrid_product_discount_amount' => 'Product\\DiscountAmount',
        'amasty_ogrid_product_base_discount_amount' => 'Product\\BaseDiscountAmount',
        'amasty_ogrid_product_qty_ordered' => 'Product\\Qty\\Ordered',
        'amasty_ogrid_product_qty_invoiced' => 'Product\\Qty\\Invoiced',
        'amasty_ogrid_product_qty_shipped' => 'Product\\Qty\\Shipped',
        'amasty_ogrid_product_qty_refunded' => 'Product\\Qty\\Refunded',
        'amasty_ogrid_product_qty_canceled' => 'Product\\Qty\\Canceled',
        'amasty_ogrid_product_qty_available' => 'Product\\Qty\\Available',
        'amasty_ogrid_product_row_total' => 'Product\\RowTotal',
        'amasty_ogrid_product_base_row_total' => 'Product\\BaseRowTotal'
    ];

    protected $_orderTrack = [
        'amasty_ogrid_sales_shipment_track' => 'Tracking\\Number'
    ];

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Amasty\Ogrid\Model\Indexer\IndexerHandlerFactory $indexerHandlerFactory,
        \Amasty\Ogrid\Model\ResourceModel\Attribute\CollectionFactory $attributeCollectionFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context);
        $this->_indexerHandlerFactory = $indexerHandlerFactory;
        $this->_attributeCollectionFactory = $attributeCollectionFactory;
        $this->_scopeConfig = $context->getScopeConfig();
    }

    public function getOrderField($key)
    {
        $fields = array_merge($this->_orderFields, $this->_orderItemFields, $this->_orderTrack);
        return array_key_exists($key, $fields) ? $fields[$key] : null;
    }

    public function getOrderFields()
    {
        return $this->_orderFields;
    }

    public function getOrderItemFields()
    {
        return $this->_orderItemFields;
    }

    public function getTrackFields()
    {
        return $this->_orderTrack;
    }

    public function getAttributesFields()
    {
        $fields = [];
        foreach($this->getAttributeCollection() as $attribute){
            $fields[$attribute->getAttributeCode()] = $attribute;
        }
        return $fields;
    }

    public function getAttributeCollection()
    {
        if ($this->_attributeCollection === null){
            $dimensions = [];

            $attributesHash = $this->_indexerHandlerFactory->create(['data' => [
                'indexer_id' => \Amasty\Ogrid\Model\Indexer\Attribute::INDEXER_ID
            ]])->getIndexedAttributesHash($dimensions);

            $this->_attributeCollection = $this->_attributeCollectionFactory->create()
                ->joinProductAttributes()
                ->addFieldToFilter('main_table.attribute_code', ['in' => $attributesHash]);
        }

        return $this->_attributeCollection;
    }

    public function getScopeValue($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getHideStatuses()
    {
        if ($this->_hideStatuses === null){
            $this->_hideStatuses = [];
            $statuses = $this->getScopeValue(self::SCOPE_GENERAL_HIDE_STATUSES);
            if ($statuses !== '' && $statuses !== null) {
                $this->_hideStatuses = explode(',', $statuses);
            }
        }
        return $this->_hideStatuses;
    }
}
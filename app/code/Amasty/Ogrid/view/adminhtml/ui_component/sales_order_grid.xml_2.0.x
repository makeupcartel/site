<?xml version="1.0" encoding="UTF-8"?>
<listing xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Ui:etc/ui_configuration.xsd">
    <container name="listing_top">
        <component name="columns_controls" class="Amasty\Ogrid\Ui\Component\Columns">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="component" xsi:type="string">Amasty_Ogrid/js/grid/controls/columns</item>
                    <item name="tabsData" xsi:type="array">
                        <item name="general" xsi:type="string" translate="true">General</item>
                        <item name="product" xsi:type="string" translate="true">Product</item>
                        <item name="customer" xsi:type="string" translate="true">Customer</item>
                        <item name="billing" xsi:type="string" translate="true">Billing</item>
                        <item name="shipping" xsi:type="string" translate="true">Shipping</item>
                        <item name="unassigned" xsi:type="string" translate="true">Other</item>
                    </item>
                    <item name="productColsData" xsi:type="array">
                        <item name="amasty_ogrid_product_name" xsi:type="array">
                            <item name="visible" xsi:type="boolean">false</item>
                            <item name="filter" xsi:type="string">text</item>
                            <item name="label" xsi:type="string" translate="true">Name</item>
                        </item>
                        <item name="amasty_ogrid_product_sku" xsi:type="array">
                            <item name="visible" xsi:type="boolean">false</item>
                            <item name="filter" xsi:type="string">text</item>
                            <item name="label" xsi:type="string" translate="true">SKU</item>
                        </item>
                        <item name="amasty_ogrid_product_product_type" xsi:type="array">
                            <item name="visible" xsi:type="boolean">false</item>
                            <item name="filter" xsi:type="string">text</item>
                            <item name="label" xsi:type="string" translate="true">Type</item>
                        </item>
                        <item name="amasty_ogrid_product_product_options" xsi:type="array">
                            <item name="visible" xsi:type="boolean">false</item>
                            <item name="filter" xsi:type="string"></item>
                            <item name="label" xsi:type="string" translate="true">Options</item>
                        </item>
                        <item name="amasty_ogrid_product_base_price" xsi:type="array">
                            <item name="visible" xsi:type="boolean">false</item>
                            <item name="filter" xsi:type="string">textRange</item>
                            <item name="label" xsi:type="string" translate="true">Price (Base)</item>
                        </item>
                        <item name="amasty_ogrid_product_price" xsi:type="array">
                            <item name="visible" xsi:type="boolean">false</item>
                            <item name="filter" xsi:type="string">textRange</item>
                            <item name="label" xsi:type="string" translate="true">Price (Purchased)</item>
                        </item>
                        <item name="amasty_ogrid_product_tax_percent" xsi:type="array">
                            <item name="visible" xsi:type="boolean">false</item>
                            <item name="filter" xsi:type="string">textRange</item>
                            <item name="label" xsi:type="string" translate="true">Tax Percent</item>
                        </item>
                        <item name="amasty_ogrid_product_base_tax_amount" xsi:type="array">
                            <item name="visible" xsi:type="boolean">false</item>
                            <item name="filter" xsi:type="string">textRange</item>
                            <item name="label" xsi:type="string" translate="true">Tax Amount (Base)</item>
                        </item>
                        <item name="amasty_ogrid_product_tax_amount" xsi:type="array">
                            <item name="visible" xsi:type="boolean">false</item>
                            <item name="filter" xsi:type="string">textRange</item>
                            <item name="label" xsi:type="string" translate="true">Tax Amount (Purchased)</item>
                        </item>
                        <item name="amasty_ogrid_product_discount_percent" xsi:type="array">
                            <item name="visible" xsi:type="boolean">false</item>
                            <item name="filter" xsi:type="string">textRange</item>
                            <item name="label" xsi:type="string" translate="true">Discount Percent</item>
                        </item>
                        <item name="amasty_ogrid_product_base_discount_amount" xsi:type="array">
                            <item name="visible" xsi:type="boolean">false</item>
                            <item name="filter" xsi:type="string">textRange</item>
                            <item name="label" xsi:type="string" translate="true">Discount Amount (Base)</item>
                        </item>
                        <item name="amasty_ogrid_product_discount_amount" xsi:type="array">
                            <item name="visible" xsi:type="boolean">false</item>
                            <item name="filter" xsi:type="string">textRange</item>
                            <item name="label" xsi:type="string" translate="true">Discount Amount (Purchased)</item>
                        </item>
                        <item name="amasty_ogrid_product_qty_ordered" xsi:type="array">
                            <item name="visible" xsi:type="boolean">false</item>
                            <item name="filter" xsi:type="string">textRange</item>
                            <item name="label" xsi:type="string" translate="true">Qty Ordered</item>
                        </item>
                        <item name="amasty_ogrid_product_qty_invoiced" xsi:type="array">
                            <item name="visible" xsi:type="boolean">false</item>
                            <item name="filter" xsi:type="string">textRange</item>
                            <item name="label" xsi:type="string" translate="true">Qty Invoiced</item>
                        </item>
                        <item name="amasty_ogrid_product_qty_shipped" xsi:type="array">
                            <item name="visible" xsi:type="boolean">false</item>
                            <item name="filter" xsi:type="string">textRange</item>
                            <item name="label" xsi:type="string" translate="true">Qty Shipped</item>
                        </item>
                        <item name="amasty_ogrid_product_qty_refunded" xsi:type="array">
                            <item name="visible" xsi:type="boolean">false</item>
                            <item name="filter" xsi:type="string">textRange</item>
                            <item name="label" xsi:type="string" translate="true">Qty Refunded</item>
                        </item>
                        <item name="amasty_ogrid_product_qty_canceled" xsi:type="array">
                            <item name="visible" xsi:type="boolean">false</item>
                            <item name="filter" xsi:type="string">textRange</item>
                            <item name="label" xsi:type="string" translate="true">Qty Canceled</item>
                        </item>
                        <item name="amasty_ogrid_product_base_row_total" xsi:type="array">
                            <item name="visible" xsi:type="boolean">false</item>
                            <item name="filter" xsi:type="string">textRange</item>
                            <item name="label" xsi:type="string" translate="true">Row Total (Base)</item>
                        </item>
                        <item name="amasty_ogrid_product_row_total" xsi:type="array">
                            <item name="visible" xsi:type="boolean">false</item>
                            <item name="filter" xsi:type="string">textRange</item>
                            <item name="label" xsi:type="string" translate="true">Row Total (Purchased)</item>
                        </item>
                    </item>
                    <item name="storageConfig" xsi:type="array">
                        <item name="provider" xsi:type="string">sales_order_grid.sales_order_grid.listing_top.bookmarks</item>
                        <item name="namespace" xsi:type="string">current</item>
                    </item>
                    <item name="clientConfig" xsi:type="array">
                        <item name="saveUrl" xsi:type="url" path="amasty_ogrid/index/bookmarks"/>
                        <item name="validateBeforeSave" xsi:type="boolean">false</item>
                    </item>
                </item>
            </argument>
        </component>
    </container>
    <columns name="sales_order_columns" class="Amasty\Ogrid\Ui\Component\Listing\Columns">
        <column name="increment_id">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array"><item name="tab" xsi:type="string">general</item>
                </item>
            </argument>
        </column>
        <column name="store_id">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">general</item>
                </item>
            </argument>
        </column>
        <column name="created_at">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">general</item>
                </item>
            </argument>
        </column>
        <column name="billing_name">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">billing</item>
                </item>
            </argument>
        </column>
        <column name="shipping_name">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">shipping</item>
                </item>
            </argument>
        </column>
        <column name="base_grand_total">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">general</item>
                </item>
            </argument>
        </column>
        <column name="grand_total">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">general</item>
                </item>
            </argument>
        </column>
        <column name="status">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">general</item>
                </item>
            </argument>
        </column>
        <column name="billing_address">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">billing</item>
                </item>
            </argument>
        </column>
        <column name="shipping_address">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">shipping</item>
                </item>
            </argument>
        </column>
        <column name="shipping_information">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">shipping</item>
                </item>
            </argument>
        </column>
        <column name="customer_email">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">customer</item>
                </item>
            </argument>
        </column>
        <column name="customer_group">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">customer</item>
                </item>
            </argument>
        </column>
        <column name="subtotal">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">general</item>
                </item>
            </argument>
        </column>
        <column name="shipping_and_handling">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">shipping</item>
                </item>
            </argument>
        </column>
        <column name="customer_name">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">customer</item>
                </item>
            </argument>
        </column>
        <column name="payment_method">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">general</item>
                </item>
            </argument>
        </column>
        <column name="total_refunded">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">general</item>
                </item>
            </argument>
        </column>
        <actionsColumn name="actions">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">general</item>
                </item>
            </argument>
        </actionsColumn>

        <column name="amasty_ogrid_coupon_code">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">general</item>
                    <item name="visible" xsi:type="boolean">false</item>
                    <item name="filter" xsi:type="string">text</item>
                    <item name="label" xsi:type="string" translate="true">Coupon Code</item>
                </item>
            </argument>
        </column>

        <column name="amasty_ogrid_shipping_description">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">general</item>
                    <item name="visible" xsi:type="boolean">false</item>
                    <item name="filter" xsi:type="string">text</item>
                    <item name="label" xsi:type="string" translate="true">Shipping Method</item>
                </item>
            </argument>
        </column>

        <column name="amasty_ogrid_base_subtotal" class="Magento\Sales\Ui\Component\Listing\Column\Price">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">general</item>
                    <item name="visible" xsi:type="boolean">false</item>
                    <item name="filter" xsi:type="string">textRange</item>
                    <item name="label" xsi:type="string" translate="true">Subtotal (Base)</item>
                </item>
            </argument>
        </column>
        <column name="amasty_ogrid_subtotal" class="Magento\Sales\Ui\Component\Listing\Column\PurchasedPrice">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">general</item>
                    <item name="visible" xsi:type="boolean">false</item>
                    <item name="filter" xsi:type="string">textRange</item>
                    <item name="label" xsi:type="string" translate="true">Subtotal (Purchased)</item>
                </item>
            </argument>
        </column>
        <column name="amasty_ogrid_weight">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">general</item>
                    <item name="visible" xsi:type="boolean">false</item>
                    <item name="filter" xsi:type="string">text</item>
                    <item name="label" xsi:type="string" translate="true">Weight</item>
                </item>
            </argument>
        </column>
        <column name="amasty_ogrid_shipping_fax">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">shipping</item>
                    <item name="visible" xsi:type="boolean">false</item>
                    <item name="filter" xsi:type="string">text</item>
                    <item name="label" xsi:type="string" translate="true">Fax</item>
                </item>
            </argument>
        </column>
        <column name="amasty_ogrid_shipping_region">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">shipping</item>
                    <item name="visible" xsi:type="boolean">false</item>
                    <item name="filter" xsi:type="string">text</item>
                    <item name="label" xsi:type="string" translate="true">Region</item>
                </item>
            </argument>
        </column>
        <column name="amasty_ogrid_shipping_postcode">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">shipping</item>
                    <item name="visible" xsi:type="boolean">false</item>
                    <item name="filter" xsi:type="string">text</item>
                    <item name="label" xsi:type="string" translate="true">Postcode</item>
                </item>
            </argument>
        </column>
        <column name="amasty_ogrid_shipping_city">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">shipping</item>
                    <item name="visible" xsi:type="boolean">false</item>
                    <item name="filter" xsi:type="string">text</item>
                    <item name="label" xsi:type="string" translate="true">City</item>
                </item>
            </argument>
        </column>
        <column name="amasty_ogrid_shipping_telephone">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">shipping</item>
                    <item name="visible" xsi:type="boolean">false</item>
                    <item name="filter" xsi:type="string">text</item>
                    <item name="label" xsi:type="string" translate="true">Telephone</item>
                </item>
            </argument>
        </column>
        <column name="amasty_ogrid_shipping_country_id">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">shipping</item>
                    <item name="visible" xsi:type="boolean">false</item>
                    <item name="filter" xsi:type="string">text</item>
                    <item name="label" xsi:type="string" translate="true">Country</item>
                </item>
            </argument>
        </column>
        <column name="amasty_ogrid_billing_fax">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">billing</item>
                    <item name="visible" xsi:type="boolean">false</item>
                    <item name="filter" xsi:type="string">text</item>
                    <item name="label" xsi:type="string" translate="true">Fax</item>
                </item>
            </argument>
        </column>
        <column name="amasty_ogrid_billing_region">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">billing</item>
                    <item name="visible" xsi:type="boolean">false</item>
                    <item name="filter" xsi:type="string">text</item>
                    <item name="label" xsi:type="string" translate="true">Region</item>
                </item>
            </argument>
        </column>
        <column name="amasty_ogrid_billing_postcode">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">billing</item>
                    <item name="visible" xsi:type="boolean">false</item>
                    <item name="filter" xsi:type="string">text</item>
                    <item name="label" xsi:type="string" translate="true">Postcode</item>
                </item>
            </argument>
        </column>
        <column name="amasty_ogrid_billing_city">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">billing</item>
                    <item name="visible" xsi:type="boolean">false</item>
                    <item name="filter" xsi:type="string">text</item>
                    <item name="label" xsi:type="string" translate="true">City</item>
                </item>
            </argument>
        </column>
        <column name="amasty_ogrid_billing_telephone">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">billing</item>
                    <item name="visible" xsi:type="boolean">false</item>
                    <item name="filter" xsi:type="string">text</item>
                    <item name="label" xsi:type="string" translate="true">Telephone</item>
                </item>
            </argument>
        </column>
        <column name="amasty_ogrid_billing_country_id">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="tab" xsi:type="string">billing</item>
                    <item name="visible" xsi:type="boolean">false</item>
                    <item name="filter" xsi:type="string">text</item>
                    <item name="label" xsi:type="string" translate="true">Country</item>
                </item>
            </argument>
        </column>
        <column name="amasty_ogrid_items_ordered">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="visible" xsi:type="boolean">false</item>
                    <item name="filter" xsi:type="boolean">false</item>
                    <item name="sortable" xsi:type="boolean">false</item>
                    <item name="component" xsi:type="string">Amasty_Ogrid/js/grid/columns/items_ordered</item>
                    <item name="componentProvider" xsi:type="string">sales_order_grid.sales_order_grid.listing_top.columns_controls</item>

                    <item name="label" xsi:type="string" translate="true">Product Details</item>
                    <item name="listingFiltersProvider" xsi:type="string">sales_order_grid.sales_order_grid.listing_top.listing_filters</item>
                </item>
            </argument>
        </column>
    </columns>
</listing>
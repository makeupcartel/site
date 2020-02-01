/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
], function (quote, priceUtils) {
    'use strict';
    return function (Component) {
        return Component.extend({
            /**
             * @return {String}
             */
            getShippingMethodTitle: function () {
                var shippingMethod = quote.shippingMethod();
                return shippingMethod ? shippingMethod['carrier_title'] + ' - ' + this.getValue() : '';
            },
            /**
             * @return {*}
             */
            getValue: function () {
                var price = quote.totals()['shipping_amount'];

                return this.getFormattedPrice(price);
            },
            /**
             * @param {*} price
             * @return {*|String}
             */
            getFormattedPrice: function (price) {
                return priceUtils.formatPrice(price, quote.getPriceFormat());
            }
        });
    }
});

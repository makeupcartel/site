define(
    [
        'jquery',
        'underscore',
        'Swarming_SubscribePro/js/view/cart/subscription',
        'Swarming_SubscribePro/js/model/product/price',
        'priceBox'
    ],
    function ($, _, Component, productPriceModel) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Swarming_SubscribePro/product/subscription',
                oneTimePurchasePriceTemplate: 'Swarming_SubscribePro/product/price/default/one-time-purchase',
                subscriptionPriceTemplate: 'Swarming_SubscribePro/product/price/default/subscription',
                priceBoxSelector: '[data-role=priceBox]',
                qtyFieldSelector: '#qty',
                product: {},
                priceConfig: {},
                productPrice: {},
                priceBoxElement: null
            },

            initialize: function () {
                var self = this;
                this._super();
                this.priceBoxElement = this.getPriceBoxElement();
                this.priceBoxElement.on('reloadPrice', this.onPriceChange.bind(this));
                this.initProductPrice();
                this.priceBoxElement.trigger('reloadPrice');
                $('.product-options-bottom .tabs li').each(function(){
                    $(this).on('click', self.onClick.bind($(this)));
                })
            },

            initProductPrice: function () {
                this.productPrice = productPriceModel.create(this.product, this.priceConfig);
            },

            onPriceChange: function () {
                var priceBox = this.priceBoxElement.data('mage-priceBox');
                if (!priceBox || !priceBox.cache || !priceBox.cache.displayPrices) {
                    return;
                }

                this.syncProductPrice(priceBox.cache.displayPrices);
            },

            syncProductPrice: function (prices) {
                var frontendFinalPrice, frontendPrice;
                var code = this.getFrontendPriceCode();
                if (prices[code]) {
                    frontendFinalPrice = frontendPrice = prices[code].amount;
                }
                if (prices.oldPrice) {
                    frontendPrice = prices.oldPrice.amount;
                }
                this.productPrice.setFrontendPrice(frontendFinalPrice);
                this.productPrice.hasSpecialPrice(frontendPrice != frontendFinalPrice);
            },

            getPriceBoxElement: function () {
                var priceBoxElement = _.find($(this.priceBoxSelector), function(el) {
                    return el && $(el).data('mage-priceBox');
                });
                return $(priceBoxElement);
            },

            getFrontendPriceCode: function () {
                var code = 'finalPrice';
                if (this.priceConfig.displayPriceExcludingTax && this.priceConfig.priceIncludesTax) {
                    code = 'basePrice';
                }

                return code;
            },
            
            onClick: function(){
                var tab_id = $(this).attr('data-tab');
                $('ul.tabs li').removeClass('active');
                $('.tab-content').removeClass('current');

                $(this).addClass('active');
                $("#" + tab_id).addClass('current');
                if (tab_id === 'tab-2') {
                    $('.subscription-container label[for="option_subscription"]').trigger('click');
                    $('.subscription-container input#option_subscription').trigger('click');
                    $('#product-addtocart-button span').text(subscribe);
                } else {
                    $('.subscription-container label[for="option_oneTimePurchase"]').trigger('click');
                    $('.subscription-container input#option_oneTimePurchase').trigger('click');
                    if ($('#product-addtocart-button').hasClass('disabled')) {
                        $('#product-addtocart-button span').text(buttonOutOfStock);
                    } else {
                        $('#product-addtocart-button span').text(buttonTitle);
                    }

                }
            }
        });
    }
);

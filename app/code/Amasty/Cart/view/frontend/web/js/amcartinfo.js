define([
    "jquery",
    "jquery/ui",
    "Magento_Customer/js/customer-data"
], function ($, ui, customerData) {

    $.widget('mage.amCartInfo', {
        customerData: customerData,
        productIds: [],
        productInfo: '.product-item-info',
        productImage: '.product-image-photo',
        productQty: '.product-image-wrapper .cart-info .qty',
        cartInfo: '.cart-info',

        _create: function () {
            this.displayAddedQty();
        },

        displayAddedQty: function () {
            var self = this;
            this.element.on('contentUpdated', function () {
                self.updateCartInfo();
            });
        },

        updateCartInfo: function() {
            var self = this;
            var items = customerData.get('cart')().items;
            var productsInCart = [];
            for (var i = 0; i < items.length; i++) {
                var productId = items[i].product_id,
                    product = this.getProduct(productId);
                if (product.length == 0) {
                    continue;
                }
                if (productsInCart[productId]) {
                    productsInCart[productId] = productsInCart[productId] + items[i].qty;
                } else {
                    productsInCart[productId] = items[i].qty;
                }
                if (typeof this.productIds[productId] === 'undefined') {
                    this.productIds[productId] = items[i].qty;
                    this.addHover(product, this.productIds[productId]);
                } else if (this.productIds[productId] != productsInCart[productId]) {
                    this.productIds[productId] = productsInCart[productId];
                    this.updateQty(product, this.productIds[productId]);
                }
            }

            this.productIds.forEach(function (element, index, object) {
                if (!productsInCart[index]) {
                    object.splice(index, 1);
                    self.removeHover(self.getProduct(index));
                }
            })
        },

        getProduct: function (productId) {
            var selector = '[data-product-id="' + productId + '"], ' +
                '[id="product-price-' + productId + '"], ' +
                '[name="product"][value="' + productId + '"]';
            var product = $(selector);

            return product.first();
        },

        addHover: function (product, qty) {
            var productInfo = product.closest(this.productInfo);
            if (productInfo.length > 0) {
                var productImage = productInfo.find('.product-image-photo');
                if (productImage.length > 0) {
                    var cartInfo = $('<div></div>')
                            .css('display', 'none')
                            .attr('class', 'cart-info'),
                        qtyDiv = $('<div></div>')
                            .attr('class', 'qty')
                            .html(qty),
                        messageDiv = $('<div></div>')
                            .html(this.options['infoMessage']);
                    cartInfo.append(qtyDiv);
                    cartInfo.append(messageDiv);
                    productImage.parent().append(cartInfo);
                    productInfo.on('mouseover', function () {
                        if (productImage.parent().find(this.cartInfo).length > 0) {
                            productImage.addClass('mask');
                            $(productImage.parent()).find('.cart-info').show();
                        }
                    }.bind(this));
                    productInfo.on('mouseleave', function () {
                        if (productImage.parent().find(this.cartInfo).length > 0) {
                            productImage.removeClass('mask');
                            $(productImage.parent()).find('.cart-info').hide();
                        }
                    }.bind(this));
                }
            }
        },

        updateQty: function (product, qty) {
            var productInfo = product.closest(this.productInfo);
            if (productInfo.length > 0) {
                var productQty = productInfo.find(this.productQty);
                if (productQty.length > 0) {
                    productQty.html(qty);
                }
            }
        },

        removeHover: function (product) {
            var productInfo = product.closest(this.productInfo);
            if (productInfo.length > 0) {
                var productImage = productInfo.find(this.productImage);
                if (productImage.length > 0) {
                    var cartInfo = productImage.parent().find(this.cartInfo);
                    if (cartInfo.length > 0) {
                        cartInfo.remove();
                    }
                }
            }
        }
    });

    return $.mage.amCartInfo;
});

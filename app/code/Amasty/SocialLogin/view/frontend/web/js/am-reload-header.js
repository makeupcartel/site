define([
    "jquery",
    "Magento_Customer/js/customer-data",
    "Magento_PageCache/js/page-cache"
], function ($, customerData, pageCache) {
    'use strict';

    var amReloadHeader = {
        ajaxReload: function (data) {
            var header = $('.header.links'),
                qty = null;

            header.find('li').each(function (index, element) {
                if (!$(element).hasClass('welcome')) {
                    element.remove();
                }
            });

            $(data.content).children().each(function (index, element) {
                header.first().append(element);
            });
            header.trigger('contentUpdated');

            var nameBlock = header.find('.customer-welcome span[data-bind*="customer().fullname"]');
            if (nameBlock.length && data.customer_name) {
                nameBlock.html(data.customer_name);
            }

            qty = header.find('.wishlist .qty');
            if (qty.length && !qty.html()) {
                qty.remove();
            }

            $.mage.formKey();
            $("[data-am-js='am-login-popup']").amLoginPopup('init');
        },

        customRedirect: function (data) {
            if (this._isShouldReloadPage()) {
                data.redirect = '2';//reload page
            }

            switch (data.redirect) {
                case "0":
                    this.ajaxReload(data);
                    break;
                case "1":
                    setTimeout(function() {
                        window.location.href = data.url;
                    }, 2000);
                    break;
                case "2":
                default:
                    setTimeout(function() {
                        window.location.reload(true);
                    }, 2000);
            }

            customerData.invalidate(['customer', 'cart', 'wishlist']);
            customerData.reload(['customer', 'cart', 'wishlist'], true);
        },

        _isShouldReloadPage: function () {
            var url = window.location.pathname,
                shouldReload = url === "/customer/account/create"
                || url === "/customer/account/create/"
                || url === "/customer/account/login"
                || url === "/customer/account/login/";

            shouldReload = shouldReload || $('body').hasClass('checkout-index-index');

            return shouldReload;
        }
    };

    return amReloadHeader;
});

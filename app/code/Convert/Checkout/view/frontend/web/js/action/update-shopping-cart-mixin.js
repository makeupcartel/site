define([
    'jquery',
    "Magento_Checkout/js/action/get-totals",
    "Magento_Customer/js/customer-data",
    "Amasty_Promo/js/popup"
], function ($, getTotalsAction, customerData) {
    return function(widget){
        $.widget('mage.updateShoppingCart', widget, {
            /** @inheritdoc */
            _create: function () {
                var self = this;
                this._super();
                this._on(this.element,{
                    'change': this.onSubmit
                })
                $('span.cart-qty').click(function(event){
                    if ($(this).hasClass('btn-qty-minus')) {
                        var qty = parseInt(jQuery(this).closest('.control').find('input.qty').val()) - 1;
                        if (qty > 0) {
                            jQuery(this).closest('.control').find('input.qty').val(qty);
                        }
                    } else {
                        var qty = parseInt(jQuery(this).closest('.control').find('input.qty').val()) + 1;
                        jQuery(this).closest('.control').find('input.qty').val(qty);
                    }
                    self.onSubmit(event);
                })
            },
            /**
             * Validates updated shopping cart data.
             *
             * @param {String} url - request url
             * @param {Object} data - post data for ajax call
             */
            validateItems: function (url, data) {
                $.extend(data, {
                    'form_key': $.mage.cookies.get('form_key')
                });

                $.ajax({
                    url: BASE_URL + url,
                    data: data,
                    type: 'post',
                    dataType: 'json',
                    context: this,

                    /** @inheritdoc */
                    beforeSend: function () {
                        $(document.body).trigger('processStart');
                    },

                    /** @inheritdoc */
                    complete: function () {
                        $(document.body).trigger('processStop');
                    }
                })
                .done(function (response) {
                    if (response.success) {
                        var r = $.Deferred();
                        var a = $.parseHTML(response.cart);
                        $("#form-validate").replaceWith(a),
                        customerData.reload(["cart", "messages"], !0);
                        getTotalsAction([], r);
                        this.uploadPromoItems(response)
                        this.onSuccess();
                    } else {
                        this.onError(response);
                    }
                })
                .fail(function () {
                    this.submitForm();
                });
            },
            /**
             * Form validation succeed.
             */
            onSuccess: function () {
                $(document).trigger('ajax:' + this.options.eventName);
            },
            /**
             * reload promo items
             */
            uploadPromoItems: function(t)
            {
                var overlay = $('[data-role=ampromo-overlay]');
                overlay.ampromoPopup({
                    sourceUrl: t.sourceUrl,
                    uenc:   t.uenc,
                    slickSettings: {
                        slidesToShow: 3,
                        slidesToScroll: 3,
                        dots: true,
                        infinite: false,
                        respondTo: 'slider',
                        responsive: [
                            {
                                breakpoint: 571,
                                settings: {
                                    slidesToShow: 2,
                                    slidesToScroll: 2
                                }
                            },
                            {
                                breakpoint: 281,
                                settings: {
                                    slidesToShow: 1,
                                    slidesToScroll: 1
                                }
                            }
                        ]
                    },
                    commonQty: t.commonQty,
                    products: JSON.parse(t.products),
                    formUrl: t.formUrl,
                    selectionMethod: t.selectionMethod,
                    giftsCounter: t.giftsCounter,
                    autoOpenPopup: t.autoOpenPopup ? 1 : 0
                });
                overlay.on('reloaded', function (e, itemsCount) {
                    $('.ampromo-items-add').toggle(itemsCount > 0)
                });

                $('[data-role=ampromo-popup-show]').click(function (){
                    overlay.ampromoPopup('show');
                    return false;
                });
                overlay.ampromoPopup('reload');
            }
        })
        return $.mage.updateShoppingCart;
    }
})
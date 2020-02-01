define([
    'jquery',
    'uiClass',
    'Amasty_Groupcat/js/fancybox/jquery.fancybox'
], function ($, Class, fancybox) {
    return Class.extend({
        products: {},
        customer: null,
        initialize: function (product) {
            this.products[product['id']] = product['name'];
            this.customer = product['customer'];
            this.url = product['url'];
            this._createButtonObserver(product['id']);

            var form = $('#amhideprice-form'),
                gdpr = form.find('[name="gdpr"]'),
                container = form.find('.amhideprice-fields-container');
            if (gdpr.length) {
                gdpr.on('change', function () {
                    if ($(this).is(':checked')) {
                        container.removeClass('amhideprice-hidden');
                    } else {
                        container.addClass('amhideprice-hidden');
                    }
                    $.fancybox.update();
                });
            }
        },

        _createButtonObserver: function (id) {
            var self = this;
            $('[data-amhide="AmastyHidePricePopup"][data-product-id="' + id + '"]').click(function(){
                self._showPopup(this);
            });
        },

        _showPopup: function (element) {
            var id = $(element).attr('data-product-id');
            var form = this._getFilledForm(id);
            var gdprCheckbox = $('#amhideprice-gdpr');
            if (gdprCheckbox.length === 1 && gdprCheckbox[0].checked === true) {
                gdprCheckbox[0].checked = false;
                $('.amhideprice-fields-container').addClass('amhideprice-hidden');
            }
            $.fancybox(form);
        },

        _getFilledForm: function (id) {
            var form = $('#amhideprice-form');
            var product = this.products[id];

            var productName = form.find('.product-name');
            if (productName && product) {
                productName.html(product);
            }

            var productId = form.find('[name="product_id"]');
            if (productId) {
                productId.val(id);
            }

            var name = form.find('[name="name"]');
            if (name && this.customer.name) {
                name.val(this.customer.name);
            }

            var phone = form.find('[name="phone"]');
            if (phone && this.customer.phone) {
                phone.val(this.customer.phone);
            }

            var email = form.find('[name="email"]');
            if (email && this.customer.email) {
                email.val(this.customer.email);
            }

            var self = this;
            form.on('submit', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                e.stopPropagation();
                var validator = $(this).validation({ radioCheckboxClosest: '.nested'});
                if (validator.valid()) {
                    self.submitForm($(this));
                }
                return false;
            });

            return form;
        },

        submitForm: function (form) {
            form = $(form);
            var self = this;

            var data = form.serialize();
            this._clearForm(form);
            $.ajax({
                url: self.url,
                data: data,
                type: 'post',
                dataType: 'json',
                beforeSend: function() {
                    $.fancybox.close();
                    $.fancybox.showLoading();
                },
                success: function(response) {
                    $.fancybox.hideLoading();
                    var result = $('<div/>',
                        {class: 'message'}
                    );
                    var html = $('<div/>');
                    if (response.success) {
                        html.html(response.success).appendTo(result);
                        result.addClass('success');
                    }

                    if (response.error) {
                        html.html(response.error).appendTo(result);
                        result.addClass('error');
                    }

                    $.fancybox(result);
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    $.fancybox.hideLoading();
                    $.fancybox(errorThrown);
                }
            });
        },

        _clearForm: function(form) {
            var key = form.find('[name="form_key"]');
            var value = key.val();
            $(form)[0].reset();
            key.val(value);
        }
    });
});

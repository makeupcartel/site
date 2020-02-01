define([
    "jquery",
    "jquery/ui",
    'mage/translate',
    'Magento_Catalog/js/catalog-add-to-cart',
    'Magento_Catalog/product/view/validation'
], function ($, ui, transl, mage_addtocart, validation) {
    $.widget('mage.amCart', {
        options: {},
        progressSelector: '#amprogress',
        imageWrapperSelector: 'div.fotorama__active, .product-image-wrapper, #amasty-main-container',
        formParentSelector: '.product-item, .product.info, .item, .main',
        topCartSelector: '[data-block="minicart"]',
        addToCartButtonDisabledClass: 'disabled',
        addToCartButtonSelector: '.action.tocart',

        _create: function (options) {
            var self = this;
            if (this.element.prop("tagName") == "BUTTON" || this.element.prop("tagName") == "A") {
                this._createButtonObserve(this.element);
            } else {
                if ($('body').hasClass('checkout-cart-configure')) {
                    this.options['send_url'] = this.options['send_url'].replace('/add', '/UpdateItemOptions');
                }
                this.element.unbind("submit");
                this.element.on('submit', function (e) {
                    if ($(this).find('input[data-role="pp-checkout-url"][name="return_url"]').length > 0) {
                        return;//disable for paypal button
                    }

                    e.preventDefault();
                    e.stopImmediatePropagation();
                    e.stopPropagation();
                    $(this).addClass('am-validation-form');
                    $('.amcart-error').remove();
                    var validator = $(this).validation({radioCheckboxClosest: '.nested'});
                    if (validator.valid() && !$(this).data('amcart-processing')) {
                        $(this).data('amcart-processing', 1);
                        self.submitForm($(this));
                    } else if ($(this).has('.product-options-wrapper').length === 0) {
                        self.moveErrors();
                    }

                    return false;
                });
            }
        },

        _createDataPostAttribute: function (element) {
            var parent = element.parents('.product-item').first();
            if (parent) {
                var priceElement = parent.find('[data-product-id]').first();
                if (priceElement.length) {
                    var id = priceElement.attr('data-product-id');
                    var url = this.options['send_url'].replace('amasty_cart/cart/add', 'amasty_cart/cart/post');
                    var _currentElement = element;
                    $.ajax({
                        url: url,
                        data: 'product=' + id,
                        type: 'post',
                        dataType: 'json',
                        success: function (response) {
                            if (_currentElement) {
                                _currentElement.attr('data-post', response);
                            }
                        }
                    });
                }
            }
        },

        _createButtonObserve: function (element) {
            if (element.parents('form[data-role="tocart-form"]').length) {
                return;
            }

            if (!element.attr('data-post')) {
                this._createDataPostAttribute(element);
            }

            element.unbind("click");
            var self = this;
            element.on('click', function (e) {
                var dataPost = element.attr('data-post');
                if (dataPost) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    e.stopPropagation();
                    dataPost = jQuery.parseJSON(dataPost);
                    var form = $('<form />', {action: dataPost.action, method: 'POST'});
                    $.each(dataPost.data, function (key, value) {
                        form.append(
                            $('<input />', {name: key, type: 'hidden', value: value})
                        );
                    });
                    form.append(
                        $('<input />', {name: 'form_key', type: 'hidden', value: $('input[name="form_key"]').val()})
                    );
                    var parent = element.closest(self.formParentSelector);
                    if (parent.find('input[name^="qty"]').length) {
                        form.append(
                            parent.find('input[name^="qty"]').clone().hide()
                        );
                    }
                    parent.append(form);
                    self.submitForm(form);
                }
            });
        },

        submitForm: function (form) {
            var self = this;
            if (form.has('input[type="file"]').length && form.find('input[type="file"]').val() !== '') {
                self.element.off('submit');
                form.submit();
            } else {
                self.ajaxSubmit(form);
            }
        },

        submitFormInPopup: function () {
            var form = $('#confirmBox form');
            if (form.length) {
                var validator = form.validation({radioCheckboxClosest: '.nested'});
                if (validator.valid()) {
                    this.submitForm(form);
                } else {
                    this.moveErrors();
                }
            }
        },

        ajaxSubmit: function (form) {
            $('#confirmBox, #confirmOverlay').fadeOut(function () {
                $(this).remove();
            });
            var self = this;
            $(this.topCartSelector).trigger('contentLoading');
            self.disableButton(form);
            var data = form.serialize();
            data += '&product_page=' + $('body').hasClass('catalog-product-view');
            /*
            * add data from swatches
            * */
            if (form.find('input[name="product"]').length) {
                var input = form.find('input[name="product"]')[0],
                    productId = $(input).val(),
                    popupSwatches = $('.swatch-opt-' + productId);
                if (productId
                    && popupSwatches.length
                    && popupSwatches.find('.amconf-matrix-observed').length == 0
                ) {
                    var swatchesData = '&' + $('.swatch-opt-' + productId + ' :input').serialize();
                    if (swatchesData.indexOf("''") === -1
                        && swatchesData.indexOf("=&") === -1
                    ) {
                        data += swatchesData;
                    }
                }
            }
            if (form.attr('action') && form.attr('action').length) {
                var idProduct = form.attr('action').match(/product(.*?)uenc/);
                if (idProduct) {
                    idProduct = idProduct[0].replace(/[^\d]/gi, '');
                    if (parseInt(idProduct) > 0) {
                        data += '&product=' + idProduct;
                    }
                }
                var position = form.attr('action').indexOf('/id/');
                if (position > 0) {
                    id = form.attr('action').substr(position + 4, form.attr('action').length);
                    if (parseInt(id) > 0) {
                        data += '&id=' + parseInt(id);
                    }
                }
            }
            var url = self.options['send_url'];
            if (form.attr('action') && form.attr('action').indexOf('wishlist/index/cart') > 0) {
                url = form.attr('action').replace('wishlist/index/cart', 'amasty_cart/wishlist/cart');
                var quoteItemId = form.find('[name="item"]').val();
                if (quoteItemId) {
                    data += '&id=' + quoteItemId;
                }
            }
            $.ajax({
                url: url,
                data: data,
                type: 'post',
                dataType: 'json',
                beforeSend: function () {
                    self.showAnimation(form);
                },

                success: function (response) {
                    self.hideAnimation();
                    if (response.error) {
                        alert(response.error);
                    } else if (response.is_add_to_cart === 1 && self.options.open_minicart) {
                        self.isObserverEnabled = true;
                        $('[data-block=\'minicart\']').on('contentUpdated', function () {
                            if (self.isObserverEnabled) {
                                $('[data-block="minicart"]').find('[data-role="dropdownDialog"]').dropdownDialog('open');
                                self.isObserverEnabled = false;
                            }

                        });
                    } else {
                        if (response.redirect) {
                            window.location = response.redirect;
                            return;
                        }
                        try {
                            self.confirm({
                                'title': response.title,
                                'message': response.message,
                                'related': response.related,
                                'checkout': response.checkout,
                                'cart': response.cart,
                                'buttons': {
                                    '1': {
                                        'name': response.b1_name,
                                        'class': 'am-btn-left',
                                        'timer': response.timer,
                                        'action': function () {
                                            if (response.b1_action.indexOf('document.location') > -1
                                                && window.parent.location != window.location
                                            ) {
                                                response.b1_action = response.b1_action.replace('document.location', 'window.parent.location');
                                            }
                                            eval(response.b1_action);
                                        }
                                    },
                                    '2': {
                                        'name': response.b2_name,
                                        'class': 'am-btn-right',
                                        'action': function () {
                                            if (response.b2_action.indexOf('document.location') > -1
                                                && window.parent.location != window.location
                                            ) {
                                                response.b2_action = response.b2_action.replace('document.location', 'window.parent.location');
                                            }
                                            eval(response.b2_action);
                                        }
                                    }
                                }
                            });
                            eval(response.script);
                            $("#product_addtocart_form").trigger('contentUpdated');

                            $('[data-role="swatch-options"]').on('swatch.initialized', function ($, selectedAttributes, event) {
                                if (selectedAttributes && event) {
                                    $.each(selectedAttributes, $.proxy(function ($, attributeId, optionId) {
                                        $(this).find('.swatch-attribute' +
                                            '[attribute-id="' + attributeId + '"] [option-id="' + optionId + '"]'
                                        ).first().trigger('click');
                                    }, event.currentTarget, $));
                                }
                            }.bind(this, $, response.selected_options));


                            var popup = $('#confirmBox');
                            if (popup.find('.related').length) {
                                popup.find('button.tocart').each(function (i, button) {
                                    self._createButtonObserve($(button));
                                })
                            }

                            if (response.is_minipage) {
                                popup.addClass('amcart-minipage-wrap');
                            }

                            if (typeof initStamped == 'function') {
                                initStamped();
                            } else {
                                console.log(1);
                            }

                        } catch (e) {
                            console.warn(e);
                        }
                    }

                    if (response.is_add_to_cart === 1 && response.product_sku) {
                        $(document).trigger('ajax:addToCart', response.product_sku);
                    }

                    if (response.customer_wishlist) {
                        $('#wishlist-view-form').html($(response.customer_wishlist).html());
                    }
                }
            }).always(function () {
                self.enableButton(form);
                form.data('amcart-processing', 0);
            });

            return false;
        },

        showAnimation: function (form) {
            var foundImage = false;
            var loadingType = this.options['type_loading'];
            if (loadingType != 0) {
                try {
                    var parent = form.closest(this.formParentSelector);
                    var wrapper = parent.find(this.imageWrapperSelector);
                    wrapper = $(wrapper[0]);
                    var image = wrapper.find('img');
                    var topCart = $(this.topCartSelector);
                    if (image.length && topCart.length) {
                        image = $(image[0]);
                        var clonedImage = image.clone();
                        clonedImage.css({
                            'maxWidth': '100%',
                            'opacity': 1,
                            'position': 'relative'
                        });
                        foundImage = true;
                        var container = $('<div />', {
                            id: "am_loading_container",
                            css: {
                                position: 'absolute',
                                zIndex: '99919',
                                top: 0,
                                left: 0
                            }
                        });
                        container.append(clonedImage);
                        wrapper.append(container);

                        var posImage = image.offset(),
                            posLink = topCart.offset();
                        $('body').append(
                            container.css({
                                top: posImage.top,
                                left: posImage.left
                            })
                        );
                        container.animate({
                            opacity: 0.15,
                            left: posLink.left + 'px',
                            top: posLink.top + 'px',
                            width: 0,
                            height: 0
                        }, 1500, function () {
                            container.remove();
                        });
                    }
                } catch (ex) {
                    foundImage = false;
                }
            }

            if (loadingType == 0 || !foundImage) {
                var progress = $('<div />', {id: "amprogress"}),
                    container = $('<div />', {id: "amimg-container"}),
                    img = $('<img />');
                container.appendTo(progress);

                img.attr('src', this.options['src_image_progress']);
                img.appendTo(container);
                container.width('150px');

                var width = container.width();
                width = "-" + width / 2 + "px";
                container.css("margin-left", width);
                progress.hide().appendTo($('body')).fadeIn();
            }
        },

        hideAnimation: function () {
            if ($(this.progressSelector).length) {
                $(this.progressSelector).fadeOut(function () {
                    $(this).remove();
                });
            }
        },
        //run every second while time !=0
        oneSec: function () {
            var elem = $('#confirmButtons .timer'),
                value = elem.text(),
                sec = parseInt(value.replace(/\D+/g, ""));
            if (sec) {
                value = value.replace(sec, sec - 1);
                elem.text(value);
                if (sec <= 1) {
                    clearInterval(document.timer);
                    elem.click();
                }
            } else {
                clearInterval(document.timer);
            }
        },

        confirm: function (params) {
            var wrapper = $('#confirmBox, #confirmOverlay');
            if (wrapper.length > 0) {
                wrapper.remove();
            }
            var buttonHTML = '',
                checkoutButton = params.checkout ? params.checkout : '',
                value;
            $.each(params.buttons, function (name, obj) {
                value = obj['name'];
                if (obj['timer']) {
                    value += obj['timer'];
                }
                // Generating the markup for the buttons:
                buttonHTML += '<button class="' + 'button ' + obj['class'] + '" title="' + obj['name'] + '">' + value + '</button>';
                if (!obj.action) {
                    obj.action = function () {
                    };
                }
            });
            var confirmOverlay = $('<div />', {
                id: "confirmOverlay"
            });
            var hideDiv = $('<div />', {
                id: "hideDiv"
            });
            hideDiv.appendTo(confirmOverlay);
            var confirmBox = $('<div />', {
                id: "confirmBox"
            });
            switch (this.options['align']) {
                case "1":
                    confirmBox.addClass('am-top');
                    break;
                case "2":
                    confirmBox.addClass('am-top-left');
                    break;
                case "3":
                    confirmBox.addClass('am-top-right');
                    break;
                case "4":
                    confirmBox.addClass('am-left');
                    break;
                case "5":
                    confirmBox.addClass('am-right');
                    break;
                case "0":
                default:
                    confirmBox.addClass('am-center');
            }
            confirmOverlay.hide().appendTo($('body'));
            var cross = $('<span title="' + $.mage.__("Close") + '" class="cross"></span>').html('&times;');
            cross.click(function () {
                self.confirmHide();
            });
            confirmBox.append(cross);
            var confirmButtons = $('<div />', {
                id: "confirmButtons",
                class: 'amcart-confirm-buttons'
            });
            confirmButtons.html(buttonHTML + checkoutButton);
            confirmButtons.appendTo(confirmBox);
            var messageBox = $('<div />', {
                id: "messageBox",
                class: 'amcart-message-box'
            });
            messageBox.html(params.message);
            messageBox.insertBefore(confirmButtons);
            var relatedBox = $('<div />', {
                class: "am-related-box"
            });
            relatedBox.html(params.related);
            relatedBox.insertAfter(confirmButtons);
            confirmBox.hide().appendTo($('body')).fadeIn();
            confirmOverlay.fadeIn();
            var self = this;
            hideDiv.click(function () {
                self.confirmHide();
            });
            var buttons = $('#confirmButtons button'),
                i = 0;
            $.each(params.buttons, function (name, obj) {
                buttons.eq(i++).click(function () {
                    obj.action();
                    return false;
                });
            });
            this.initQtyControls();
            this.confirmTimer();
        },

        confirmTimer: function () {
            var elem = $('#confirmButtons .timer'),
                value = elem.text(),
                sec = parseInt(value.replace(/\D+/g, ""));
            if (sec) {
                var self = this;
                document.timer = setInterval(function () {
                    self.oneSec();
                }, 1000);
            }
        },

        confirmHide: function () {
            $('#confirmBox, #confirmOverlay').fadeOut(function () {
                $(this).remove();
            });
        },

        disableButton: function (form) {
            var addToCartButton = $(form).find(this.addToCartButtonSelector);
            addToCartButton.addClass(this.addToCartButtonDisabledClass);
        },

        enableButton: function (form) {
            var self = this,
                addToCartButton = $(form).find(this.addToCartButtonSelector);
            addToCartButton.removeClass(self.addToCartButtonDisabledClass);
        },

        moveErrors: function () {
            $('.am-validation-form div.mage-error, #confirmBox div.mage-error')
                .addClass('amcart-error')
                .each(function (index, element) {
                    var attr = $(element).attr('for').match(/\[(.*?)\]/)[1];
                    if (attr.length > 0) {
                        $(element)
                            .closest('.product-item-details, #confirmBox')
                            .find(".swatch-attribute[attribute-id=\"" + attr + "\"]")
                            .append(element);
                    }
                });
        },

        minusQtyClick: function (e) {
            var qtyElement = $(e.target).siblings('.amcart-input');

            if (qtyElement.length) {
                var qty = parseInt(qtyElement.val()),
                    decrement = 1;

                if (qty >= decrement) {
                    qty -= decrement;
                    qtyElement.val(qty);
                    qtyElement.trigger('change');
                }
            }
        },

        plusQtyClick: function (e) {
            var qtyElement = $(e.target).siblings('.amcart-input');

            if (qtyElement.length) {
                var qty = parseInt(qtyElement.val()),
                    increment = 1,
                    availableQty = qtyElement.attr('max');

                qty += increment;
                if (!availableQty || availableQty >= qty) {
                    qtyElement.val(qty);
                    qtyElement.trigger('change');
                }
            }
        },

        amOnChange: function (e, self) {
            $(e.target).siblings('[data-amcart="qty-refresh"]').css('visibility', 'visible');
            $('#confirmButtons .timer').remove();

            if (event.keyCode === 13) {
                self.amAddToCart();
            }
        },

        amAddToCart: function () {
            var url = this.options['send_url'].replace('/add', '/update'),
                qtyWrap = $('[data-amcart="qty-wrap"]'),
                qty = $('[data-amcart="qty-input"]')[0].value,
                data = '';

            if (qty > 0) {
                qtyWrap.find('input').each(function (index, item) {
                    data += "&" + $(item).attr('name') + "=" + $(item).val();
                });

                $.ajax({
                    url: url,
                    data: data,
                    type: 'post',
                    dataType: 'json',
                    showLoader: true,
                    success: function (response) {
                        if (response.error) {
                            alert(response.error);
                        } else {
                            $('[data-amcart="amcart-count"]').text(response.items);
                            $('[data-amcart="amcart-price"]').html(response.subtotal);
                        }
                    }
                });
            }
        },

        initQtyControls: function () {
            var self = this;

            $('[data-amcart="qty-plus"]').on('click', this.plusQtyClick);
            $('[data-amcart="qty-minus"]').on('click', this.minusQtyClick);
            $('[data-amcart="qty-refresh"]').on('click', this.amAddToCart.bind(this));
            $('[data-amcart="qty-input"]').on('change keyup', function (e) {
                self.amOnChange(e, self);
            });
        }
    });

    return $.mage.amCart;
});

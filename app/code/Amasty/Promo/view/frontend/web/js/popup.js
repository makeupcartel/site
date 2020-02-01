define([
    "jquery",
    "Amasty_Promo/js/discount-calculator",
    "uiRegistry",
    "Amasty_Promo/js/slick.min",
    "jquery/ui",
    'priceOptions'
], function ($, discount, registry) {

    $.widget('mage.ampromoPopup', {
        options: {
            slickSettings: {},
            sourceUrl: '',
            uenc: '',
            commonQty: 0,
            products: {},
            promoSku: {},
            formUrl: '',
            selectionMethod: 0,
            giftsCounter: 0,
            autoOpenPopup: 0
        },

        isSlickInitialized: false,
        isMultipleMethod: 1,
        isEnableGiftsCounter: 1,
        isOpen: true,
        /**
         *
         * @private _create
         */
        _create: function () {
            this.options.promoSku = ('triggered_products' in this.options.products)
                ? this.options.products.promo_sku : this.options.promoSku;
            this.options.products = ('triggered_products' in this.options.products)
                ? this.options.products.triggered_products : null;

            $(this.element).mousedown($.proxy(function (event) {
                if ($(event.target).data('role') == 'ampromo-overlay') {
                    event.stopPropagation();
                    this.hide();
                }
            }, this));

            $('[data-role="ampromo-popup-hide"]').click($.proxy(this.hide, this));

            var widget = this;
            $(document).on('reloadPrice', function (item) {
                widget.options.promoSku = ('triggered_products' in widget.options.products)
                    ? widget.options.products.promo_sku : widget.options.promoSku;
                discount.update(widget.options.promoSku, item);
            });
        },

        initOptions: function () {
            if (parseInt(this.options.selectionMethod) === this.isMultipleMethod) {
                this.initMultipleProductAdd();
            } else {
                this.initOneByOneProductAdd();
            }

            if (parseInt(this.options.giftsCounter) === this.isEnableGiftsCounter) {
                this.initProductQtyState();
                this.addCounterToPopup();
                if (parseInt(this.options.selectionMethod) === this.isMultipleMethod) {
                    this.addToCartDisableOrEnable(true);
                }
            }
        },

        /**
         * @returns {mage.ampromoPopup}
         */
        initMultipleProductAdd: function () {
            var self = this;

            $.each($(this.element).find('[data-role=ampromo-product-select]'), function () {
                $(this).children().unbind('click').click(function () {
                    $(this).prop("checked", !$(this).prop("checked"));
                });
            });

            $.each($(this.element).find('[data-am-js=ampromo-qty-input]'), function () {
                $(this).keyup(function () {
                    self.checkAddButton();
                    $.validator.validateSingleElement(this);
                });

                $(this).mouseenter(function () {
                    $('[data-role=ampromo-gallery]').slick("slickSetOption", "draggable", false, false);
                });

                $(this).mouseleave(function () {
                    $('[data-role=ampromo-gallery]').slick("slickSetOption", "draggable", true, true);
                });
            });

            $.each($(this.element).find('[data-role=ampromo-item]'), function () {
                var slickStartTransform,
                    slickEndTransform,
                    excludedTags,
                    checkbox,
                    qtyInput;
                excludedTags = ['INPUT', 'SELECT', 'LABEL', 'TEXTAREA'];
                $(this).unbind('mousedown').mousedown(function (e) {
                    slickStartTransform = $('.slick-track').css('transform');
                }).unbind('mouseup').mouseup(function (e) {
                    slickEndTransform = $('.slick-track').css('transform');
                    if (slickStartTransform == slickEndTransform) {
                        checkbox = $(this).find('[data-role=ampromo-product-select] input');
                        qtyInput = $(this).find('[data-am-js=ampromo-qty-input]');
                        if (e.target == checkbox[0] ||
                            (e.target == qtyInput[0] && qtyInput.prop('disabled')) ||
                            ($.inArray(e.target.tagName, excludedTags) == -1 && $.inArray(e.target.parentElement.tagName, excludedTags) == -1)) {
                            $(this).toggleClass('-selected');
                            checkbox.prop("checked", !checkbox.prop("checked"));
                            self.checkboxState(checkbox[0]);
                            self.checkAddButton();
                        }
                    }
                });
            });

            $(this.element).find('[data-am-js=ampromo-add-button]').unbind('click').click(function () {
                self.sendForm();
            });

            self.checkAddButton();

            return this;
        },

        initOneByOneProductAdd: function () {
            $.each($(this.element).find('[data-role=ampromo-item]'), function () {
                var self = this;

                if ($(this).find('.ampromo-options .fieldset .field').length != 0) {
                    $(this).find('.tocart').unbind('click').on('click', function () {
                        $('.ampromo-item.-selected').removeClass('-selected');
                        $(self).addClass('-selected');
                    });
                }
            });
        },

        checkAddButton: function () {
            var self = this,
                stateCheckbox = false,
                stateInputs = true;

            $.each($(this.element).find('[data-role=ampromo-product-select]'), function (index, value) {
                if ($(this).children().prop('checked')) {
                    stateCheckbox = true;
                }
            });

            $.each($(this.element).find('[data-am-js=ampromo-qty-input]'), function (index, value) {
                if ($(this).val() < 0 && !$(this).prop('disabled')) {
                    stateInputs = false;
                    return false;
                }
            });

            if (stateCheckbox && stateInputs) {
                self.addToCartDisableOrEnable(false);
            } else {
                self.addToCartDisableOrEnable(true);
            }
        },

        /**
         * @returns {mage.ampromoPopup}
         */
        addCounterToPopup: function () {
            var self = this;

            $.each(this.options.products, function (ruleId, ruleData) {
                $.each(ruleData['sku'], function (index, itemData) {
                    self.options.products[ruleId]['sku'][index]['old_value'] = itemData.qty;
                    self.options.products[ruleId]['sku'][index]['initial_value'] = itemData.qty;
                });
            });

            return this;
        },


        /**
         * hide
         */
        hide: function () {
            $(this.element).fadeOut();
        },

        /**
         * show
         */
        show: function () {
            if (this.isSlickInitialized) {
                $('[data-role=ampromo-gallery]').slick('destroy');
                this.init();
            }

            if (parseInt(this.options.selectionMethod) === this.isMultipleMethod) {
                this.checkAddButton();
            }

            $(this.element).fadeIn();
        },

        /**
         * @returns {mage.ampromoPopup}
         */
        initProductQtyState: function () {
            var self = this;

            this.setQtys();
            $.each($(this.element).find('[data-role=ampromo-item-qty-input]').find(':input'), function (index, value) {
                $(this).unbind('keyup').keyup(function (checkbox) {
                    self.changeQty(
                        $(this),
                        $(checkbox.target.parentElement.parentElement).find('.ampromo-product-select input')[0].checked
                    );
                    self.checkAddButton();
                    $.validator.validateSingleElement(this);
                });
            });

            return this;
        },

        /**
         *
         * @returns {mage.ampromoPopup}
         */
        init: function () {
            this.options.commonQty = ('common_qty' in this.options.products)
                ? this.options.products.common_qty : this.options.commonQty;
            this.options.promoSku = ('triggered_products' in this.options.products)
                ? this.options.products.promo_sku : this.options.promoSku;
            this.options.products = ('triggered_products' in this.options.products)
                ? this.options.products.triggered_products : this.options.products;

            this.initOptions();

            // Hack for "slick" library
            $(this.element).show();
            $('[data-role=ampromo-gallery]').not('.slick-initialized').slick(this.options.slickSettings);
            $(this.element).hide();

            this.isSlickInitialized = true;

            $('.ampromo-items-form').mage('validation');

            return this;
        },

        /**
         *
         * @returns {mage.ampromoPopup}
         */
        reload: function () {
            this.isSlickInitialized = false;
            var widget = this;

            $.ajax({
                url: this.options.sourceUrl,
                method: 'GET',
                data: {uenc: this.options.uenc},
                success: function (response) {
                    var container = $('[data-role="ampromo-items-container"]');
                    response = JSON.parse(response);
                    container.html(response.popup);
                    widget.options.products = JSON.parse(response.products);
                    if (container.children().length) {
                        widget.init();
                    }
                    container.trigger('contentUpdated');

                    var itemsCount = +widget.element.find('[data-role="ampromo-gallery"]').data('count');
                    $('.ampromo-popup-title').css('max-width', (itemsCount >= 3 ? 3 * 280 : itemsCount * 280) + 'px');
                    var event = new $.Event('reloaded');
                    widget.element.trigger(event, [itemsCount]);

                    if (widget.options.autoOpenPopup && widget.isOpen && container.children().length) {
                        $('[data-role="ampromo-popup-show"]').click();
                        widget.show();
                        widget.isOpen = false;
                        return false;
                    }
                }
            });

            return this;
        },

        /**
         * sendForm
         */
        sendForm: function () {
            var formData = this.prepareFormData();

            this.addToCartDisableOrEnable(true);
            $.ajax({
                type: "POST",
                url: this.options.formUrl,
                data: {uenc: this.options.uenc, data: formData},
                success: function () {
                    location.reload();
                }
            });
        },

        /**
         *
         * @returns {Array}
         */
        prepareFormData: function () {
            var formData = [],
                re = /\[(.*?)\]/;

            $.each($(this.element).find("[data-role=ampromo-items-form]"), function (index, value) {
                if (!$(value).find("input[type='checkbox']").attr('checked')) {
                    return true;
                }

                var a = {},
                    links = [];

                formData[index] = $(value).serializeArray().reduce(function (obj, item) {
                    if (item.name.indexOf('super_attribute') >= 0 || item.name.indexOf('options') >= 0) {
                        var key = item.name.match(re)[1],
                            keyName = item.name.indexOf('super_attribute') >= 0 ? 'super_attribute' : 'options';

                        a[key] = item.value;
                        obj[keyName] = a;
                    } else if (item.name.indexOf('links[]') >= 0) {
                        links.push(item.value);
                        obj['links'] = links;
                    } else {
                        obj[item.name] = item.value;
                    }

                    return obj;
                }, {});
            });

            return formData;
        },

        /**
         *
         * @returns {mage.ampromoPopup}
         */
        setQtys: function () {
            this.updateCommonQty();
            this.updateProductLeftQty();

            return this;
        },

        /**
         *
         * @param elem
         * @param checked
         * @returns {mage.ampromoPopup}
         */
        changeQty: function (elem, checked) {
            var newQty = $(elem).val(),
                productSku = this.getProductSku(elem),
                ruleId = this.getRuleId(elem),
                ruleType = this.getRuleType(elem);

            this.updateValues(newQty, productSku, ruleId, ruleType, checked, elem);
            this.setQtys();

            return this;
        },

        /**
         *
         * @param newQty
         * @param productSku
         * @param ruleId
         * @param ruleType
         * @param checked
         * @returns {mage.ampromoPopup}
         */
        updateValues: function (newQty, productSku, ruleId, ruleType, checked, elem) {
            if (!this.isNumber(newQty) && newQty != 0) {
                return this;
            }

            var sumQty = 0,
                self = this,
                newValue = 0,
                countOfThisFreeItem = 0,
                countOfRulesFreeItem = 0,
                sumQtyInputsValues = 0,
                sumQtyByRuleId,
                ruleDiscountAmount,
                itemRuleType;

            $.each(this.options.products, function (itemRuleId, value) {
                ruleDiscountAmount = value.discount_amount;
                $.each(value['sku'], function (index, value) {
                    sumQtyByRuleId = self.getSumQtysByRuleId()[itemRuleId];
                    sumQtyInputsValues = 0;
                    countOfThisFreeItem = +self.options.promoSku[productSku].qty;

                    $.each($('[data-am-js=ampromo-qty-input]'), function () {
                        sumQtyInputsValues += parseInt(this.value, 10);
                    });

                    if (ruleType == 1 && itemRuleId == ruleId) {
                        if (sumQtyByRuleId > countOfThisFreeItem) {
                            newQty = newQty - (sumQtyByRuleId - countOfThisFreeItem);
                            $(elem).val(newQty);
                        }

                        if (newQty === "") {
                            newValue = countOfThisFreeItem - sumQtyByRuleId;
                            if (!newValue) {
                                return self;
                            }
                            sumQty = newValue - value.qty;
                        } else if (newQty < (countOfThisFreeItem - (sumQtyByRuleId - newQty))) {
                            newValue = countOfThisFreeItem - sumQtyByRuleId;
                        } else {
                            newValue = 0;
                            newQty = countOfThisFreeItem;
                        }

                        self.setProductQty(ruleId, index, newQty, value, newValue);
                    }

                    if (ruleType == 0 && itemRuleId == ruleId && productSku == index) {
                        if (newQty > countOfThisFreeItem) {
                            newQty = countOfThisFreeItem;
                            $(elem).val(newQty);
                        }

                        if (newQty == 0 || newQty == "") {
                            newValue = countOfThisFreeItem;
                        } else if (newQty <= countOfThisFreeItem) {
                            newValue = countOfThisFreeItem - parseInt(newQty, 10);
                        } else {
                            newValue = 0;
                            newQty = countOfThisFreeItem;
                        }

                        self.setProductQty(ruleId, index, newQty, value, newValue);
                    }
                });
            });

            $.each(this.options.products, function (itemRuleId, value) {
                ruleDiscountAmount = value.discount_amount;
                itemRuleType = self.options.products[itemRuleId].rule_type;

                switch (itemRuleType) {
                    case '1':
                        countOfRulesFreeItem += +value['sku'][Object.keys(value['sku'])[0]].initial_value ;
                        break;
                    case '0':
                        $.each(self.options.products[itemRuleId].sku, function (itemSku, value) {
                            countOfRulesFreeItem += +value.initial_value;
                        });
                        break;
                }
            });

            if (self.getSumQtys() < countOfRulesFreeItem) {
                this.options.commonQty = countOfRulesFreeItem - self.getSumQtys();
            } else {
                this.options.commonQty = 0;
            }

            if (newQty === "" || newQty == "0") {
                self.addToCartDisableOrEnable(true);
            } else {
                self.addToCartDisableOrEnable(false);
            }

            return this;
        },

        /**
         *
         * @returns {number}
         */
        getSumQtys: function () {
            var sumQtys = 0;

            $.each($('[data-am-js=ampromo-qty-input]'), function (index, value) {
                if (this.value != "" && this.value >= 0) {
                    sumQtys += parseInt(this.value, 10);
                }
            });

            return sumQtys;
        },

        /**
         *
         * @returns {object}
         */
        getSumQtysByRuleId: function () {
            var sumQtysByRuleId = {};

            $.each($('[data-am-js=ampromo-qty-input]'), function () {
                itemRuleId = $(this).attr('data-rule');
                if (this.value != "" && this.value >= 0) {
                    if (sumQtysByRuleId[itemRuleId]) {
                        sumQtysByRuleId[itemRuleId] += +this.value;
                    } else {
                        sumQtysByRuleId[itemRuleId] = +this.value;
                    }

                }
            });

            return sumQtysByRuleId;
        },

        /**
         *
         * @param ruleId
         * @param index
         * @param initValue
         * @returns {*}
         */
        getSumQtyValue: function (ruleId, index, initValue) {
            return initValue - this.options.products[ruleId]['sku'][index].qty;
        },

        /**
         *
         * @param ruleId
         * @param index
         * @param newQty
         * @param value
         * @param newValue
         */
        setProductQty: function (ruleId, index, newQty, value, newValue) {
            this.options.products[ruleId]['sku'][index].qty =
                (parseInt(newQty, 10) == value.old_value
                    || this.isNumber(newValue)
                    || newValue == 0)
                    ? newValue
                    : value.old_value;
        },

        /**
         *
         * @param value
         * @returns {*}
         */
        isNumber: function (value) {
            var parseValue = parseInt(value, 10),
                isValid = $.isNumeric(value);

            if (!isValid || parseValue < 0) {
                this.addToCartDisableOrEnable(true);

                return false;
            }

            return parseValue;
        },

        /**
         *
         * @returns {mage.ampromoPopup}
         */
        updateCommonQty: function () {
            $(this.element).find('[data-role=ampromo-popup-common-qty]').html(this.options.commonQty);

            return this;
        },

        /**
         *
         * @returns {mage.ampromoPopup}
         */
        updateProductLeftQty: function () {
            var self = this;

            $.each(this.options.products, function (ruleId, rulesData) {
                var id = ruleId,
                    ruleType = rulesData['rule_type'];
                $.each(rulesData['sku'], function (index, value) {
                    var productDomBySku = self.getProductDomBySku(index);
                    if (productDomBySku) {
                        $(productDomBySku.find('[data-role=ampromo-item-qty-input]')
                            .find('.ampromo-item-qty-left').find('span')).html(+value.qty);
                        var qtyInput = $(productDomBySku.find('[data-role=ampromo-item-qty-input]')
                            .find('.ampromo-qty'));
                        if (qtyInput.length) {
                            qtyInput[0].setAttribute('data-rule', id);
                            qtyInput[0].setAttribute('data-rule-type', ruleType);
                        }
                    }
                });
            });

            return this;
        },

        /**
         *
         * @param elem
         * @returns {mage.ampromoPopup}
         */
        checkboxState: function (elem) {
            var productId = this.getProductId(elem),
                selectInput = $(this.getProductDomByProductId(productId)
                    .find('[data-role=ampromo-item-qty-input]')
                    .find(':input')),
                isChecked = $(elem).attr('checked');

            isChecked ? selectInput.val(1) : selectInput.val(0);

            selectInput.keyup().prop('disabled', !isChecked);

            return this;
        },

        /**
         *
         * @param state
         * @returns {mage.ampromoPopup}
         */
        addToCartDisableOrEnable: function (state) {
            $(this.element).find('[data-role=ampromo-item-buttons]').find(":button").attr('disabled', state);

            return this;
        },

        /**
         *
         * @param elem
         */
        getProductId: function (elem) {
            return this.getProductDomByElem(elem).attr('data-product-id');
        },

        /**
         *
         * @param elem
         */
        getProductSku: function (elem) {
            return this.getProductDomByElem(elem).attr('data-product-sku');
        },

        /**
         *
         * @param elem
         */
        getRuleId: function (elem) {
            return this.getProductDomByElem(elem).find('.ampromo-qty').attr('data-rule');
        },

        /**
         *
         * @param elem
         */
        getRuleType: function (elem) {
            return this.getProductDomByElem(elem).find('.ampromo-qty').attr('data-rule-type');
        },

        /**
         *
         * @param elem
         * @returns {*|jQuery}
         */
        getProductDomByElem: function (elem) {
            return $(elem).parents('[data-role=ampromo-item]');
        },

        /**
         *
         * @param sku
         * @returns {boolean}
         */
        getProductDomBySku: function (sku) {
            return this.getProductDom('data-product-sku', sku);
        },

        /**
         *
         * @param productId
         * @returns {boolean}
         */
        getProductDomByProductId: function (productId) {
            return this.getProductDom('data-product-id', productId);
        },

        /**
         *
         * @param attribute
         * @param value
         * @returns {boolean}
         */
        getProductDom: function (attribute, value) {
            var result = false;

            $.each($(this.element).find('[data-role=ampromo-item]'), function (index, item) {
                if (value == $(this).attr(attribute)) {
                    result = $(this);
                }
            });

            return result;
        }
    });

    return $.mage.ampromoPopup;
});
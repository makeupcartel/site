define([
    'jquery',
    'jquery/ui',
    'quickSearch-original'
], function ($) {
    'use strict';

    $.widget('mage.amXsearchFormMini', $.mage.quickSearch, {
        ajaxRequest: null,
        timer: null,
        delay: 500,
        minSizePopup: 700,
        sizePopupBreakpoint: 550,
        mobileView: 768,
        windowWidth: $(window).width(),
        proportionSide: 0.33,
        options: {
            url: null,
            responseFieldElements: '.amsearch-item',
            currentUrlEncoded: null,
            minChars: 5
        },
        selectors: {
            loader: '[data-amsearch-js="loader"]'
        },

        _create: function () {
            this.options = $.extend(true, this.options, window.xsearch_options);
            this.responseList = {
                indexList: null,
                selected: null
            };
            this.autoComplete = $(this.options.destinationSelector);
            this.searchForm = $(this.options.formSelector);
            this.submitBtn = this.searchForm.find(this.options.submitBtn)[0];
            this.searchLabel = $(this.options.searchLabel);
            this.redirectUrl = null;

            this.createLoader();
            this.createCloseIcon();
            /*this.createLoupeIcon();*/
            this.createSearchWrapper();
            this.createOverlay();
            this.defineHideOrClear();

            _.bindAll(this, '_onKeyDown', '_onPropertyChange', '_onSubmit', 'onClick');
            this.submitBtn.disabled = true;
            this.element.attr('autocomplete', this.options.autocomplete);

            var timer;
            this.element.on('blur', $.proxy(function () {
                timer = setTimeout($.proxy(function () {
                    this._updateAriaHasPopup(false);
                }, this), 250);
            }, this));

            this.element.trigger('blur');
            this.element.on('focus', $.proxy(function () {
                if (timer != null) {
                    clearTimeout(timer);
                }

                this.searchLabel.addClass('active');
            }, this));

            this.element.on('keydown', this._onKeyDown);
            var ua = window.navigator.userAgent,
                msie = ua.indexOf("MSIE ");

            if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
                $(this.element).keyup(this._onPropertyChange);
            } else {
                this.element.on('input propertychange', this._onPropertyChange);
            }

            // fix close overlay for mobile iOS
            $('[data-amsearch-js="overlay"]').on('click', function () {
                $(this).hide();
                $('#search_autocomplete').removeClass('showing');
            });

            this.element.on('input click', this.onClick);
            this.searchForm.on('submit', $.proxy(function (e) {
                this._onSubmit(e);
                this._updateAriaHasPopup(false);
            }, this));

            this.updatePreloadSection();
        },

        updatePreloadSection: function () {
            var $preload = $('[data-amsearch-js="preload"]'),
                self = this;
            if ($preload && $preload.html) {
                $.get(
                    self.options.url.slice(0, -1) + 'recent',
                    {uenc: self.currentUrlEncoded},
                    $.proxy(function (data) {
                        if (data && data.html) {
                            $preload.html(data.html);
                        }
                    }, this)
                );
            }
        },

        onClick: function () {
            if (this.element[0] != document.activeElement) {
                return false;//fix for IE(IE trigger input event when placeholder changed)
            }

            var preload = $('[data-amsearch-js="preload"]');
            if (preload && preload.length > 0) {
                this.showPopup(preload.html());
            } else {
                this.getEmptyRequest();
            }

            var value = this.element.val().trim(),
                minChars = this.options.minChars ? this.options.minChars : this.options.minSearchLength;
            if (value.length >= parseInt(minChars, 10)
                && this.ajaxRequest
                && this.ajaxRequest.readyState !== 1
            ) {
                this._onPropertyChangeCallBack();
            }
        },

        _onSubmit: function (e) {
            var value = this.element.val().trim();

            if (value.length === 0 || value == null || /^\s+$/.test(value)) {
                e.preventDefault();
            }

            if (this.redirectUrl) {
                e.preventDefault();
                window.location.assign(this.redirectUrl);
            }
        },

        showPopup: function (data) {
            var self = this,
                searchField = this.element,
                dropdown = $('<div class="amsearch-results" data-amsearch-js="results"></div>'),
                searchResults = $('<div class="amsearch-leftside" data-amsearch-js="left-side"></div>'),
                sideProportion = this.proportionSide,
                productResults = '[data-amsearch-js="products"]',
                leftSide = '[data-amsearch-js="left-side"]',
                leftSideWidth,
                productsWidth,
                defaultSearchBlock = this.searchForm.width(),
                inputWrapper = '[data-amsearch-js="search-wrapper-input"]',
                popularSearch = '[data-search-block-type="popular_searches"]',
                recentSearch = '[data-search-block-type="recent_searches"]',
                overlay = '[data-amsearch-js="overlay"]',
                closeLoupeIcons = '[data-amsearch-js = "close"], [data-amsearch-js="loupe"]';

            dropdown.append(searchResults);

            if ($.type(data) == 'string') {
                searchResults.append(data);
                this.searchForm.addClass('amsearch-form-container');
            } else {
                for (var i in data) {
                    if (data[i].type === 'product'
                        && this.options.width >= this.sizePopupBreakpoint
                        && this.windowWidth >= this.mobileView
                    ) {
                        dropdown.append(data[i].html);
                    } else {
                        searchResults.append($(data[i].html).addClass(data[i].type));
                    }
                }
            }

            this.changePopupFlow();

            this.responseList.indexList = this.autoComplete.html(dropdown)
                .addClass('amsearch-clone-position')
                .show()
                .find(this.options.responseFieldElements + ':visible');

            this.autoComplete.trigger('contentUpdated');
            $(popularSearch).parent('.amsearch-item-container').addClass('popular_searches');
            $(recentSearch).parent('.amsearch-item-container').addClass('recent_searches');

            if (this.options.width >= this.sizePopupBreakpoint) {
                leftSideWidth = $(productResults).length ? this.options.width * sideProportion : searchField.outerWidth();
                productsWidth = this.options.width ? this.options.width * (1 - sideProportion) : searchField.outerWidth();
                $(productResults).addClass('-columns');
            } else {
                leftSideWidth = $(productResults).length ? this.options.width : searchField.outerWidth();
            }

            $(closeLoupeIcons).appendTo($(inputWrapper)).show();
            $(overlay).show();

            if (this.windowWidth >= this.mobileView) {
                $(inputWrapper).css('width', '100%');
                this.searchForm.find('.search-autocomplete').css('width', defaultSearchBlock);
            }

            /*  this.searchForm.addClass('-opened').find('.input-text').attr('placeholder', $.mage.__('Enter Keyword or Item'));*/
            this.searchForm.keydown(function (eventObject) {
                if (eventObject.which == 27) {
                    self.hidePopup();
                }
            });

            $(leftSide).css('width', leftSideWidth);
            $(productResults).css('width', productsWidth);

            if (!$(leftSide).children().length) {
                $(leftSide).hide();
            }

            if (!$(leftSide).children('.amsearch-item-container').length) {
                $(productResults).css('width', '100%');
            }

            this._resetResponseList(false);
            this.element.removeAttr('aria-activedescendant');

            if (this.responseList.indexList.length) {
                this._updateAriaHasPopup(true);
            } else {
                this._updateAriaHasPopup(false);
            }

            this.responseList.indexList
                .on('click', function (e) {
                    var $target = $(e.target);
                    if ($target.hasClass('amasty-xsearch-block-header')) {
                        return false;
                    }

                    if (!$target.attr('data-click-url')) {
                        $target = $(e.target).closest('[data-click-url]');
                    }
                    if ($(e.target).closest('[data-amsearch-js="item-actions"]').length === 0
                        && $(e.target).closest('[data-amsearch-js="product-item"]').length
                    ) {
                        document.location.href = $target.attr('data-click-url');
                    } else {
                        this.element.focus();
                        this.element.trigger('focus');
                        this.element.blur();
                    }
                }.bind(this))
                .on('mouseenter mouseleave', function (e) {
                    if (this.responseList && this.responseList.indexList) {
                        this.responseList.indexList.removeClass(this.options.selectClass);
                    }

                    $(e.target).addClass(this.options.selectClass);
                    this.responseList.selected = $(e.target);
                    this.element.attr('aria-activedescendant', $(e.target).attr('id'));
                }.bind(this));

            return defaultSearchBlock;
        },

        changePopupFlow: function () {
            if (this.options.width < this.sizePopupBreakpoint) {
                this.searchForm.addClass('-small');
            } else if (this.options.width >= this.minSizePopup) {
                this.searchForm.addClass('-large');
            }
        },

        hidePopup: function () {
            $('#search_autocomplete').removeClass('showing');
            var defaultSearchBlock = this.showPopup(),
                inputWrapper = '[data-amsearch-js="search-wrapper-input"]';

            if (this.autoComplete.is(':hidden')) {
                this.searchLabel.removeClass('active');
            }

            this.autoComplete.hide();
            $('[data-amsearch-js="overlay"], [data-amsearch-js="close"], [data-amsearch-js="loupe"]').hide();
            /*this.searchForm.find('.input-text').attr('placeholder', $.mage.__('Search entire store here...'));*/
            this.searchForm.removeClass('-opened');

            if (this.windowWidth >= this.mobileView) {
                $(inputWrapper).css('width', '100%');
                this.searchForm.find('.search-autocomplete').css('width', defaultSearchBlock);
            }
        },

        clearField: function () {
            $('#search_autocomplete').removeClass('showing');
            var searchField = this.element,
                preload = $('[data-amsearch-js="preload"]'),
                defaultSearchBlock = this.showPopup(),
                leftSide = '[data-amsearch-js="left-side"]',
                inputWrapper = '[data-amsearch-js="search-wrapper-input"]';

            searchField.val('').focus();
            this.showPopup(preload.html());

            if (this.windowWidth >= this.mobileView) {
                $(inputWrapper).css('width', '100%');
                this.searchForm.find('.search-autocomplete').css('width', defaultSearchBlock);
                $(leftSide).css('width', defaultSearchBlock);
            }

            this.defineExistencePopup();
        },

        outputNotFound: function () {
            var result = $('[data-amsearch-js="products"]').length,
                dropdown = $('[data-amsearch-js="results"]'),
                message = $.mage.__('Your search returned no products.'),
                leftSide = '[data-amsearch-js="left-side"]';

            if (!result) {
                $(".amsearch-results").css('border', '1px solid #999999');
                $('<div class="amsearch-products -waste">' + message + '</div>').appendTo(dropdown);
                $('#search_autocomplete').addClass('showing');
                if (this.options.width >= this.sizePopupBreakpoint) {
                    $(leftSide).css('width', this.options.width * this.proportionSide);
                } else {
                    $(leftSide).css('width', this.options.width);
                }
            }
        },

        getEmptyRequest: function () {
            var defaultSearchBlock = this.showPopup(),
                inputWrapper = '[data-amsearch-js="search-wrapper-input"]',
                overlay = '[data-amsearch-js="overlay"]',
                closeLoupeIcons = '[data-amsearch-js = "close"], [data-amsearch-js="loupe"]';

            $(closeLoupeIcons).appendTo($(inputWrapper)).show();
            $(overlay).show();

            if (this.windowWidth >= this.mobileView) {
                this.searchForm.find('.search-autocomplete').css('width', defaultSearchBlock);
                $(inputWrapper).css('width', '100%');
            }

            this.searchForm.addClass('amsearch-form-container');
            /* this.searchForm.addClass('-opened').find('.input-text').attr('placeholder', $.mage.__('Enter Keyword or Item'));*/
            this.defineExistencePopup();
        },

        defineExistencePopup: function () {
            var leftSide = '[data-amsearch-js="left-side"]',
                overlay = '[data-amsearch-js="overlay"]';

            if (!$(leftSide).children().length) {
                this.searchForm.find('.search-autocomplete').hide();
                $('#search_autocomplete').removeClass('showing');
                $(overlay).hide();
            }
        },

        _onPropertyChange: function () {
            $('#search_autocomplete').removeClass('showing');
            var self = this;
            if (this.timer != null) {
                clearTimeout(self.timer);
            }

            self.timer = setTimeout(function () {
                self._onPropertyChangeCallBack.call(this);
            }.bind(this), self.delay);
        },

        _onPropertyChangeCallBack: function () {
            var self = this,
                minChars = this.options.minChars ? this.options.minChars : this.options.minSearchLength,
                searchField = this.element,
                inputWrapper = '[data-amsearch-js="search-wrapper-input"]',
                value = this.element.val().trim();

            // check if value is empty
            this.submitBtn.disabled = (value.length === 0) || (value == null) || /^\s+$/.test(value);
            if (value.length >= parseInt(minChars, 10)) {
                this.showLoader();

                if (this.ajaxRequest) {
                    this.ajaxRequest.abort();
                }

                this.ajaxRequest = $.get(
                    self.options.url,
                    {q: value, uenc: self.currentUrlEncoded},
                    $.proxy(function (data) {
                        this.showPopup(data);
                        $('#search_autocomplete').addClass('showing');
                        this.hideLoader();
                        if (self.windowWidth >= self.mobileView) {
                            $(inputWrapper).css('width', self.options.width);
                            this.searchForm.find('.search-autocomplete').css('width', self.options.width);
                        }

                        this.outputNotFound();

                        if (data.redirect_url) {
                            this.redirectUrl = data.redirect_url;
                        } else {
                            this.redirectUrl = null;
                        }
                    }, this)
                );
            } else {
                this._resetResponseList(true);
                this.autoComplete.hide();
                this._updateAriaHasPopup(false);
                this.element.removeAttr('aria-activedescendant');
                $('#search_autocomplete').addClass('showing');
            }
        },

        defineHideOrClear: function () {
            var self = this;

            $('body').on('click', function (e) {
                var target = $(e.target);
                if (target.hasClass('amsearch-close')) {
                    self.clearField();
                }

                if (!target.closest('.amsearch-form-container').length) {
                    self.hidePopup();
                }
            });
        },

        createSearchWrapper: function () {
            var wrapper = $('<div/>', {
                class: 'amsearch-wrapper-input',
                'data-amsearch-js': 'search-wrapper-input'
            }).appendTo($(this.searchForm.find('.control')));
            $(this.searchForm.find('.input-text')).appendTo('[data-amsearch-js="search-wrapper-input"]');
        },

        createCloseIcon: function () {
            var closeIcon = $('<div/>', {
                class: 'amsearch-closes',
                title: $.mage.__('Clear Field'),
                'data-amsearch-js': 'close'
            }).appendTo(this.searchForm.find('.control'));
        },

        /* createLoupeIcon: function () {
             var loupeIcon = $('<button/>', {
                 class: 'amsearch-loupe',
                 title: $.mage.__('Search'),
                 type: 'submit',
                 'data-amsearch-js': 'loupe'
             }).appendTo(this.searchForm.find('.control'));
         },*/

        createOverlay: function () {
            var overlay = $('<div/>', {
                class: 'amsearch-overlay',
                'data-amsearch-js': 'overlay'
            }).appendTo($('body'));
        },

        createLoader: function () {
            var loader = $('<div/>', {
                'data-amsearch-js': "loader",
                class: 'amasty-xsearch-loader amasty-xsearch-hide'
            }).appendTo(this.searchForm);
        },

        showLoader: function () {
            var $loader = $(this.selectors.loader);
            $loader.removeClass('amasty-xsearch-hide');
            $(this.submitBtn).addClass('amasty-xsearch-hide');
        },

        hideLoader: function () {
            var $loader = $(this.selectors.loader);
            $loader.addClass('amasty-xsearch-hide');
            $(this.submitBtn).removeClass('amasty-xsearch-hide');
        }
    });

    return $.mage.amXsearchFormMini;
});

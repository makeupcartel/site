/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'underscore',
    'jquery',
    'swiper',
    'Magento_Ui/js/grid/listing'
], function (ko, _, $, Swiper, Listing) {
    'use strict';

    return Listing.extend({
        defaults: {
            additionalClasses: '',
            filteredRows: {},
            limit: 5,
            listens: {
                elems: 'filterRowsFromCache',
                '${ $.provider }:data.items': 'filterRowsFromServer'
            }
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.filteredRows = ko.observable();
            this.initProductsLimit();
            this.hideLoader();
        },

        /**
         * Initialize product limit
         * Product limit can be configured through Ui component.
         * Product limit are present in widget form
         *
         * @returns {exports}
         */
        initProductsLimit: function () {
            if (this.source['page_size']) {
                this.limit = this.source['page_size'];
            }

            return this;
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Listing} Chainable.
         */
        initObservable: function () {
            this._super()
                .track({
                    rows: []
                });

            return this;
        },

        /**
         * Sort and filter rows, that are already in magento storage cache
         *
         * @return void
         */
        filterRowsFromCache: function () {
            this._filterRows(this.rows);
        },

        /**
         * Sort and filter rows, that are come from backend
         *
         * @param {Object} rows
         */
        filterRowsFromServer: function (rows) {
            this._filterRows(rows);
        },

        /**
         * Filter rows by limit and sort them
         *
         * @param {Array} rows
         * @private
         */
        _filterRows: function (rows) {
            this.filteredRows(_.sortBy(rows, 'added_at').reverse().slice(0, this.limit));
        },

        /**
         * Can retrieve product url
         *
         * @param {Object} row
         * @returns {String}
         */
        getUrl: function (row) {
            return row.url;
        },

        /**
         * Get product attribute by code.
         *
         * @param {String} code
         * @return {Object}
         */
        getComponentByCode: function (code) {
            var elems = this.elems() ? this.elems() : ko.getObservable(this, 'elems'),
                component;

            component = _.filter(elems, function (elem) {
                return elem.index === code;
            }, this).pop();

            return component;
        },
        
        /* CUSTOM */
        initSlider: function() {
          /* RECENTLY VIEWED */
          setTimeout(function() {
            if ($('.block-viewed-products-grid .products-grid').length) {
              $('.block-viewed-products-grid .control .btn-qty').click(function(e) {
                e.preventDefault();
                var qtyInput = $(this).closest('.control').find('input.qty');
                if (!qtyInput.val()) {
                  qtyInput.val(1);
                } else {
                  if ($(this).hasClass('btn-qty-minus')) {
                    var val = parseInt(qtyInput.val());
                    if(val>1){
                      qtyInput.val(val-1);
                    }
                  } else {
                    var val = parseInt(qtyInput.val());
                    qtyInput.val(val+1);
                  }
                }
              });
              //console.log(customer);
              $('.block-viewed-products-grid .products-grid').addClass('swiper-container');
              $('.block-viewed-products-grid .products-grid .product-items').addClass('swiper-wrapper');
              $('.block-viewed-products-grid .products-grid .product-items .product-item').addClass('swiper-slide');
              $('.block-viewed-products-grid .products-grid').append('<div class="swiper-button-prev"></div><div class="swiper-button-next"></div><div class="swiper-pagination"></div>');
              var swiper = new Swiper('.catalog-category-view .block-viewed-products-grid .products-grid', {
                wrapperClass: 'product-items',
                slideClass: 'product-item',
                slidesPerView: 4,
                spaceBetween: 24,
                pagination: {
                  el: '.swiper-pagination',
                  clickable: true,
                },
                navigation: {
                  nextEl: '.swiper-button-next',
                  prevEl: '.swiper-button-prev',
                },
                breakpoints: {
                  // when window width is <= 767px
                  767: {
                    // slidesPerView: 'auto',
                    slidesPerView: 2,
                    spaceBetween: 16
                  },
                  // when window width is <= 1199px
                  1199: {
                    slidesPerView: 3,
                    spaceBetween: 24
                  }
                },
                on: {
                  init: function () {
                    setTimeout(function () {
                      alignHeight('.catalog-category-view .block-viewed-products-grid .product-item .product-item-info .product-item-name');
                      alignHeight('.catalog-category-view .block-viewed-products-grid .product-item .product-item-info .price-box');
                    }, 1000);
                  }
                }
              });
              var swiper2 = new Swiper('.catalog-product-view .block-viewed-products-grid .products-grid', {
                wrapperClass: 'product-items',
                slideClass: 'product-item',
                slidesPerView: 4,
                spaceBetween: 24,
                pagination: {
                  el: '.swiper-pagination',
                  clickable: true,
                },
                navigation: {
                  nextEl: '.swiper-button-next',
                  prevEl: '.swiper-button-prev',
                },
                breakpoints: {
                  // when window width is <= 767px
                  767: {
                    slidesPerView: 'auto',
                    spaceBetween: 16
                  },
                  // when window width is <= 1199px
                  1199: {
                    slidesPerView: 3,
                    spaceBetween: 24
                  }
                },
                on: {
                  init: function () {
                    setTimeout(function () {
                      alignHeight('.catalog-product-view .products-grid .product-item .product-item-info .product-item-name');
                      alignHeight('.catalog-product-view .products-grid .product-item .product-item-info .price-box');
                    }, 1000);
                  }
                }
              });
            }
            /* COMMON FUNCTION ALIGN HEIGHT */
              function alignHeight(selector) {
                  jQuery(selector).css('height', '');
                  var minHeight = 0;
                  jQuery(selector).each(function() {
                      if (minHeight < jQuery(this).outerHeight()) {
                          minHeight = jQuery(this).outerHeight()
                      }
                  });
                  if (minHeight > 0) {
                      jQuery(selector).css('height', minHeight);
                  }
              }

            /*ALIGN HEIGHT PRODUCT */
            jQuery(window).resize(function () {
                setTimeout(function () {
                    alignHeight('.catalog-product-view .products-grid .product-item .product-item-info .product-item-name');
                    alignHeight('.catalog-product-view .products-grid .product-item .product-item-info .price-box'); 
                    alignHeight('.catalog-category-view .block-viewed-products-grid .product-item .product-item-info .product-item-name');
                    alignHeight('.catalog-category-view .block-viewed-products-grid .product-item .product-item-info .price-box');
                }, 500);
            });
          }, 1000);
        }
    });
});

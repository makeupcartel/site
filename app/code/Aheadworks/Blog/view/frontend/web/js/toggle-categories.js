/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'jquery'
], function ($) {
    "use strict";

    $.widget('mage.awBlogToggleCategories', {
        showMoreSelector: '#block-category-show-more-link',
        showLessSelector: "#block-category-show-less-link",
        listingItemSelector: ".block-category-listing-item",
        showMoreClass: "show-more",
        options: {},

        _create: function () {
            this._bind();
        },

        /**
         * Event binding
         */
        _bind: function () {
            this._on({
                /**
                 * Calls callback when event is triggered
                 */
                'click #block-category-show-more-link': function () {
                    this._toggleShowMoreVisibility();
                },
                'click #block-category-show-less-link': function () {
                    this._toggleShowMoreVisibility();
                }
            });
        },

        /**
         * Toggle visibility of category listing items
         */
        _toggleShowMoreVisibility: function () {
            var options = $(this.element).find(this.listingItemSelector);

            options.each(function(index, selector) {
                if ($(selector).hasClass('show') || $(selector).hasClass('hide')) {
                    $(selector).toggleClass('show hide');
                } else if ($(selector).hasClass(this.showMoreClass)) {
                    this._toggleShowMoreLink(selector);
                } else if ($(selector).hasClass('shaded') || $(selector).hasClass('no-shaded')) {
                    $(selector).toggleClass('shaded no-shaded');
                }
            }.bind(this));
        },

        /**
         * Toggle show-more link
         * @param {string} selector
         */
        _toggleShowMoreLink: function(selector) {
            $(selector).children(this.showMoreSelector).toggleClass('show hide');
            $(selector).children(this.showLessSelector).toggleClass('show hide');
        }
    });

    return $.mage.awBlogToggleCategories;
});
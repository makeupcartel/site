/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

/**
 * Initialization widget for grid block
 *
 * @method hideBlogExcessItems()
 */
define([
    'jquery'
], function($) {
    "use strict";

    $.widget('mage.awBlogRelatedGrid', {
        options: {
            itemsSelector: '[data-aw-blog-block="items"]'
        },

        /**
         * Initialize widget
         */
        _create: function() {
            this.hideBlogExcessItems();
            $(window).on('resize', $.proxy(this.hideBlogExcessItems, this));
        },

        /**
         * Show wbtab items to fit one row
         */
        hideBlogExcessItems: function() {
            var grid = $(this.options.itemsSelector);

            if (!grid) {
                return;
            }
            var gridItems = grid.children(),
                gridWidth = grid.width(),
                itemWidth = gridItems.first().outerWidth(),
                itemsToShow = Math.round(gridWidth/itemWidth);

            gridItems.each(function(index, item) {
                if (index < itemsToShow) {
                    $(item).show();
                } else {
                    $(item).hide();
                }
            });
            this.element.css('visibility', 'visible');
        }
    });

    return $.mage.awBlogRelatedGrid;
});

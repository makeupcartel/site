/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

/**
 * Initialization widget for slider block
 *
 * @method init()
 * @method recalculateWidth()
 */
define([
    'jquery',
    'slick'
], function($) {
    "use strict";

    $.widget('mage.awBlogRelatedSlider', {
        options: {
            itemsSelector: '[data-aw-blog-block="items"]'
        },

        /**
         * Initialize widget
         */
        _create: function() {
            this.init();
            $(window).on('resize', $.proxy(this.recalculateWidth, this));
        },

        /**
         * Initialize Slick plugin
         */
        init: function()
        {
            this.recalculateWidth();
            this.element.find(this.options.itemsSelector).slick({
                adaptiveHeight: false,
                autoplay: false,
                autoplaySpeed: 3000,
                arrows: true,
                dots: false,
                pauseOnHover: true,
                pauseOnDotsHover: false,
                respondTo: 'slider',
                responsive: [
                    {
                        breakpoint: 800,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 3
                        }
                    },
                    {
                        breakpoint: 600,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 2
                        }
                    },
                    {
                        breakpoint: 400,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1,
                            adaptiveHeight: true
                        }
                    }
                ],
                slidesToShow: 4,
                slidesToScroll: 4
            });
            this.element.css('visibility', 'visible');
        },

        /**
         * Recalculation of the block width depending on the width of the screen
         */
        recalculateWidth: function() {
            var mainContent = this.element.closest('#maincontent');

            if (mainContent.length) {
                if (mainContent.width() < 768) {
                    var column = this.element.closest('.columns > .column.main, .columns > .sidebar'),
                        sliderWidth = column.length && column.width() < mainContent.width()
                            ? column.width()
                            : mainContent.width();
                    this.element.outerWidth(sliderWidth);
                    this.element.find(this.options.itemsSelector).width(this.element.width());
                } else {
                    this.element.css('width', '');
                    this.element.find(this.options.itemsSelector).css('width', '');
                }
            }
            this.element.css('visibility', 'visible');
        }
    });

    return $.mage.awBlogRelatedSlider;
});

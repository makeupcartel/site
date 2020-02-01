/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('blog.blogCommentsLink', {

        /**
         * Initialize widget
         */
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
                 * @param  {Event} event
                 */
                'click': function (event) {
                    $(location).attr('href', this.element.data('url'));
                    event.preventDefault();
                }
            });
        }
    });

    return $.blog.blogCommentsLink;
});

/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    var buttons = {
        'reset': '#reset',
        'save': '#save',
        'update': '#update',
        'schedule': '#schedule',
        'publish': '#publish',
        'saveAsDraft': '#save_as_draft',
        'saveAndContinue': '#save_and_continue'
    };

    /**
     * Initialize listener
     * @param {Function} callback
     * @param {String} action
     */
    function initListener(callback, action) {
        var selector = buttons[action],
            element = $(selector)[0];

        if (element) {
            if (element.onclick) {
                element.onclick = null;
            }
            $(element).off().on('click', callback);
        }
    }

    return {
        /**
         * Calls callback when name event is triggered
         * @param  {Object} handlers
         */
        on: function (handlers) {
            _.each(handlers, initListener);
        }
    };
});

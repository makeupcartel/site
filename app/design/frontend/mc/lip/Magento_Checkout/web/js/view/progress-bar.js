/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/step-navigator'
], function ($, _, ko, Component, stepNavigator) {
    'use strict';

    var steps = stepNavigator.steps;

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/progress-bar',
            visible: true
        },
        steps: steps,

        /** @inheritdoc */
        initialize: function () {
            var stepsValue;

            this._super();
            window.addEventListener('hashchange', _.bind(stepNavigator.handleHash, stepNavigator));

            if (!window.location.hash) {
                stepsValue = stepNavigator.steps();

                if (stepsValue.length) {
                    stepNavigator.setHash(stepsValue.sort(stepNavigator.sortItems)[0].code);
                }
            }

            stepNavigator.handleHash();
        },

        /**
         * @param {*} itemOne
         * @param {*} itemTwo
         * @return {*|Number}
         */
        sortItems: function (itemOne, itemTwo) {
            return stepNavigator.sortItems(itemOne, itemTwo);
        },

        /**
         * @param {Object} step
         */
        navigateTo: function (step) {
            stepNavigator.navigateTo(step.code);
        },

        /**
         * @param {Object} item
         * @return {*|Boolean}
         */
        isProcessed: function (item) {
            $('.opc-block-summary .payment-option._active .action.action-toggle, .opc-block-summary .items-in-cart.active div.title, .opc-block-summary .payment-option.customerbalance._active .payment-option-title').trigger('click');
            return stepNavigator.isProcessed(item.code);
        }
    });
});

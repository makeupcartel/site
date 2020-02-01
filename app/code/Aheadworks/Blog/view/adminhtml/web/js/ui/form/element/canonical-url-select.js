/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select'
], function (_, registry, Select) {
    'use strict';

    return Select.extend({
        defaults: {
            queryTemplate: 'ns = ${ $.ns }, index = category_ids'
        },

        /**
         * Init component with initialization of setHandlers method.
         */
        initialize: function () {
            this._super();
            this.setHandlers();

            return this;
        },

        /**
         * Set handler with registration on 'multiselect' value changed event
         */
        setHandlers: function () {
            registry.get(this.queryTemplate, function (component) {
                component.on('value', this.updateValue.bind(this, component));
            }.bind(this));
        },

        /**
         * Update canonical URL options
         *
         * @param {Object} component
         */
        updateValue: function (component) {
            var source = component.cacheOptions.plain,
                selectedValues = component.value(),
                currentValue = this.value(),
                result = [];

            selectedValues.forEach(function(selectedValue) {
                result.push(_.findWhere(source, {
                    value: selectedValue
                }));
            });

            this.setOptions(result);

            if (_.indexOf(selectedValues,currentValue)) {
                this.value(currentValue);
            }
        }
    });
});

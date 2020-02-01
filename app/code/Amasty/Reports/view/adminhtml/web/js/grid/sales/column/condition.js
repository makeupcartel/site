define([
    'underscore',
    'Magento_Ui/js/grid/columns/select'
], function (_, Select) {
    'use strict';

    return Select.extend({
        /**
         * @returns {String}
         */
        getLabel: function (record) {
            var label = this._super();
            if (_.isEmpty(label)) {
                label = record[this.index];
            }

            return label;
        }
    });
});

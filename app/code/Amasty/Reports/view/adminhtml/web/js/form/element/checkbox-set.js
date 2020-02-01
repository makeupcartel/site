define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/element/checkbox-set'
], function ($, _, CheckboxSet) {
    'use strict';

    return CheckboxSet.extend({
        defaults: {
            forceColumns: [
                'ids',
                'period'
            ]
        },

        /**
         * @inheritdoc
         */
        initConfig: function () {
            this._super();
            this.options = _.toArray(this.options);

            return this;
        },

        /**
         * @inheritdoc
         */
        hasChanged: function () {
            var reg = require('uiRegistry'),
                elems = reg.get('amasty_report_sales_overview_listing.amasty_report_sales_overview_listing.amreports_sales_overview_columns').elems(),
                self = this;

            _.each(elems, function (item) {
                if (item.index.indexOf('percent_') === 0) {
                    item.visible = self.value() == 1;
                } else if (self.forceColumns.indexOf(item.index) === -1) {
                    item.visible = self.value() == 0;
                }
            });
        }
    });
});

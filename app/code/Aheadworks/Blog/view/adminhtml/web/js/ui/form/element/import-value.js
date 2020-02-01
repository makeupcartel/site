/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'Magento_Catalog/js/components/import-handler'
], function (Handler) {
    'use strict';

    return Handler.extend({

        /**
         * @inheritDoc
         */
        updateValue: function (placeholder, component) {
            var value = '';

            this._super();
            value = this.value();
            value = value.replace(/_|\s{1,}/g, '-').toLowerCase();
            this.value(value);
        }
    });
});
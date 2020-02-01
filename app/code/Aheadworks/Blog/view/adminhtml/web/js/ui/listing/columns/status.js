/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'Magento_Ui/js/grid/columns/select'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Aheadworks_Blog/ui/listing/cells/status'
        },

        /**
         * Returns class that should be applied to status field
         *
         * @param Object record
         * @returns {Object}
         */
        getColorClass: function (record) {
            return record.status;
        }
    });
});

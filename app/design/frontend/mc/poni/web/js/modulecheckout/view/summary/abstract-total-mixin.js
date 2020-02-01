/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([], function () {
    'use strict';
    return function (Component) {
        return Component.extend({
            /**
             * @return {*}
             */
            isFullMode: function () {
                if (!this.getTotals()) {
                    return false;
                }
                return true;
            }
        });
    }
});

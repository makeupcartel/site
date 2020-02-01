define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select'
], function (_, uiRegistry, select) {
    'use strict';

    return select.extend({

        /**
         * Show field on actions tab
         */
        onUpdate: function () {
            if (this.visible() && uiRegistry.get(this.checkField).value() === this.showFieldOnValue) {
                uiRegistry.get(this.showField).show();
            }
        }
    });
});

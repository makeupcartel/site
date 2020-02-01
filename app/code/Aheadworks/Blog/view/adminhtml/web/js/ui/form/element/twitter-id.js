/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/abstract'
], function (Abstract) {
    'use strict';

    return Abstract.extend({

        /**
         * @inheritDoc
         */
        onUpdate: function () {
            var value = this.value();

            if (value.length && !value.includes('@')) {
                value = '@' + value;
            }
            this.value(value);

            this._super();
        }
    });
});
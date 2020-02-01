define([
    'ko',
    'underscore',
    'Magento_Ui/js/lib/spinner',
    'rjsResolver',
    'uiLayout',
    'Magento_Ui/js/grid/listing'
], function (ko, _, loader, resolver, layout, Listing) {
    'use strict';

    return Listing.extend({
        initObservable: function () {
            this._super()
                .track({
                    totals: {}
                });

            return this;
        }
    });
});

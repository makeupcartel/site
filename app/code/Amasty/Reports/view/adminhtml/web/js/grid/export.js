define([
    'jquery',
    'underscore',
    'Magento_Ui/js/grid/export',
    'mage/translate'
], function ($, _, Element, $t) {
    'use strict';

    return Element.extend({
        getParams: function () {
                 var result = this._super();
                 var data = $("#report_toolbar").serializeArray();
                 var filter = {};
                 if (_.size(data) > 0) {
                      _.each(data, function(item) {
                         filter[item.name] = item.value;
                      });
                 }
                result.amreports = filter;

            return result;
        },
    });
});

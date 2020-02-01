define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('am.reportsTabs', {
        options: {
            tabsSelect: "[data-reports-tabs='tab']"
        },

        _create: function () {
            var self = this;

            self.element.find(self.options.tabsSelect).on('click', function (e) {
                self._switchWidgetGroup($(e.target));
            });
        },

        _switchWidgetGroup: function (elem) {
            var self = this,
                widgetGroup = elem.attr('data-amrepgroup-js'),
                contentSelector = '[data-amrepgroup-js="' + widgetGroup + '"]';

            self.element.find('.-current').removeClass('-current');
            elem.addClass('-current');

            self.element.find('.-current').removeClass('-current');
            self.element.find(contentSelector).addClass('-current');
        }
    });

    return $.am.reportsTabs;
});

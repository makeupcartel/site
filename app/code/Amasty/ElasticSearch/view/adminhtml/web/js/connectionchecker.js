define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'jquery/ui'
], function ($, alert) {
    'use strict';

    $.widget('mage.connectionchecker', {
        options: {
            successText: '',
            failedText: '',
            url: '',
            elementId: '',
            fieldMapping: ''
        },

        _create: function () {
            this._on({
                'click': $.proxy(this._ping, this)
            });
        },

        _ping: function () {
            var buttonTitle = this.options.failedText,
                element =  $('#' + this.options.elementId),
                self = this;
            element.removeClass('success').addClass('fail');

            $.ajax({
                url: self.options.url,
                showLoader: true,
                data: self._collectParams()
            }).done(function (response) {
                if (response.success) {
                    element.removeClass('fail').addClass('success');
                    buttonTitle = self.options.successText;
                } else {
                    var error = response.errorMessage;
                    if (error) {
                        alert({
                            content: error
                        });
                    }
                }
            }).always(function () {
                if (buttonTitle) {
                    $('#' + self.options.elementId + '_result').text(buttonTitle);
                }
            });
        },

        _collectParams: function () {
            var params = {};
            $.each(this.options.fieldMapping, function (key, el) {
                params[key] = $('#amasty_elastic_connection_' + el).val();
            });

            return params;
        }
    });

    return $.mage.connectionchecker;
});

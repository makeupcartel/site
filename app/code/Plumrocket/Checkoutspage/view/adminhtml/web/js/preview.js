
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Checkoutspage v2.x.x
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
*/

define([
    "jquery",
    'text!Plumrocket_Checkoutspage/template/modal/preview.html',
    "jquery/ui"
], function($, template){
    "use strict";

    $.widget('checkoutspage.preview', {
        options : {
            buttonContainer: '.page-actions-buttons',
            orderIdField: '#order_id',
            popupTemplate: template 
        }, 

        _create: function() {
            $(this.options.buttonContainer).prepend(this.element);
            this.element.show();
            this.element.on('click',$.proxy(this._showPreviewPopup, this));
        },

        _initModal: function () {
            var self = this;
            this.modal = $(self.options.popupTemplate).modal({
                title: $.mage.__('Checkout Success Page Preview'),
                buttons: [],
                opened : function() {
                    $(this).find(self.options.orderIdField).val(self.options.lastOrderId);
                    $(this).find('.show-preview').on('click', function(event){
                        self._openPreviewPage(event);
                    });
                }
            });

        },

        _showPreviewPopup: function(event) {
            this._initModal();
            this.modal.modal('openModal');
        },

        _openPreviewPage: function(event) {
            var button = event.target;
            var urlKey = 'preview_' + $(button).data('action') + '_' + $(button).data('type');

            var orderId = $(this.modal).find(this.options.orderIdField).val();
            if (!orderId) {
                alert('Order ID is required');
            }

            var _previewUrl = this.options.previewUrl[urlKey];

            _previewUrl = _previewUrl.split('?')[0];
            if (_previewUrl[_previewUrl.length - 1] != '/') {
                _previewUrl += '/';
            }

            _previewUrl = _previewUrl + 'order_id/' + orderId;

            window.open(_previewUrl);
        }
    });

    return $.checkoutspage.preview;
});
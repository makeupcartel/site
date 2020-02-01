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
define(
    [
        'jquery',
        'uiComponent',
        'Magento_Customer/js/customer-data',
        'mage/validation'
    ],
    function ($, Component, customerData) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Plumrocket_Checkoutspage/registration',
                success : false,
                errorContainer : "#registration .message-error"
            },
            /** Initialize observable properties */
            initObservable: function () {
                this._super()
                    .observe('accountCreated')
                    .observe('subscribing')
                    .observe('registering')
                    .observe('success')
                    .observe('isSubscribed');
                return this;
            },
            createAccount: function(form) {

                if ($(form).validation() && $(form).validation('isValid')) {
                    var request = {},
                        $cont = $('#' + $(this).get(0).name),
                        fields = {'password' : '#password', 'password_confirmation' : '#password-confirmation'};

                    $.each(fields, function(key, value) {
                        $cont.find(value).map(function () {
                            request[key] = this.value;
                        });
                    });

                    request['subscribe']  = $(form).find('input[name=subscribe]').is(':checked') ? 1 : 0;
                    $(form).find('input[type=submit]').attr('disabled', true);

                    this.registering(true);
                    var self = this,
                        url = (!this.accountCreated()) ? this.registrationUrl : this.subscribeUrl;
                    $.post(url, request).done(
                        function(response) {
                            this.registering(false);
                            if (response.success) {
                                customerData.invalidate(["*", "messages"]);
                                this.accountCreated(true);
                                this.success(true);
                            } else {
                                self.processError(response.error_message);
                                $(form).find('input[type=submit]').attr('disabled', false);
                            }
                        }.bind(this)
                    ).fail(function(response) {
                        console.log(response);
                    });
                }
            },

            processError : function(message) {
                $(this.errorContainer).show().html('<span>' + message + '</span>');
            },

            subscribe: function() {
                if (this.accountCreated()) {
                    this.subscribing(true);
                    var self = this;

                    $.post(this.subscribeUrl, {'subscribe' : 1}).done(
                        function(response) {
                            self.subscribing(false);
                            if (response.success) {
                                self.success(true);
                                self.isSubscribed(true);
                            } else {
                                console.log(response);
                            }
                        }
                    )
                }
            },

            getLoaderSrc : function()
            {
                return this.loaderSrc;
            }
        });
    }
);

define(
    [
        'Magento_Customer/js/customer-data',
        'underscore',
        'knockout'
    ],
    function (customerData, _, ko) {
        'use strict';

        var mixin = _.extend({
            messagesObserver: {},
            staticMessages: {},
            initialize: function () {
                this._super();
                if (window.amasty_notice_disabled) {
                    return;
                }
                this.staticMessages = customerData.get('ammessages');
                this.messagesObserver = this.messages;
                this.messages = ko.computed(this.messagesMerger, this);
            },

            messagesMerger: function () {
                var messagesArr = this.messagesObserver(),
                    staticMessages = this.staticMessages(),
                    isAdded = false;

                if (messagesArr.messages == void 0) {
                    messagesArr.messages = [];
                }
                if (staticMessages.messages == void 0) {
                    staticMessages.messages = {notice: {}};
                }
                _.each(messagesArr.messages, function (message) {
                    if (_.isEqual(message, staticMessages.messages.notice)) {
                        isAdded = true;
                    }
                });
                if (!isAdded && staticMessages.messages.notice) {
                    messagesArr.messages.push(staticMessages.messages.notice);
                }

                return messagesArr;
            }
        });
        return function (target) {
            return target.extend(mixin);
        };
    }
);

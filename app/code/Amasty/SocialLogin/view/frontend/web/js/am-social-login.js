define([
    "jquery",
    "Amasty_SocialLogin/js/am-reload-header"
], function ($, amReloadHeader) {
    'use strict';

    $.widget('mage.amSocialLoginAjax',  {
        options: {
            redirect_duration: 2000,
            error_selector: '',
            tab_wrapper: '[data-am-js="am-tabs-wrapper"]',
            content_wrapper: '[data-am-js="am-content-wrapper"]'
        },

        _create: function () {
            this.init();
        },

        init: function () {
            this.socialLoginClick();
        },

        socialLoginClick: function () {
            var self = this;
            window.addEventListener("message", function(event) {
                if (event.data['redirect_data']) {
                    window.amResult(event.data);
                }
            });
            window.amResult = function (data) {
                var parent;
                $(self.options.error_selector).hide();

                if (data.result === 1) {
                    $(self.options.tab_wrapper).hide();

                    parent = $(self.options.content_wrapper).html('')
                        .addClass('am-login-success');
                    data.messages.forEach(function (message) {
                        $('<div/>').html(message).appendTo(parent);
                    });
                    parent.show();

                    amReloadHeader.customRedirect(data.redirect_data);
                } else {
                    parent = $(self.options.error_selector);
                    parent.html('');
                    data.messages.forEach(function (message) {
                        $('<div/>').html(message).appendTo(parent);
                    });
                    parent.slideDown();
                }
            };

            this.socialLoginObserve();
        },

        socialLoginObserve: function () {
            var self = this;

            $('[data-am-js="am-sl-button"]').unbind('click').click(function (event) {
                self.options.error_selector = $(event.target).parents('.am-social-login').find('[data-am-js="am-social-error"]');
                window.open(
                    event.currentTarget.href + 'isAjax/true',
                    event.currentTarget.title,
                    self.getPopupParams()
                );
                event.stopPropagation();
                event.preventDefault();

                return false;
            });
        },

        getPopupParams: function (w, h, l, t) {
            var screenX = typeof window.screenX !== 'undefined' ? window.screenX : window.screenLeft,
                screenY = typeof window.screenY !== 'undefined' ? window.screenY : window.screenTop,
                outerWidth = typeof window.outerWidth !== 'undefined' ? window.outerWidth : document.body.clientWidth,
                outerHeight = typeof window.outerHeight !== 'undefined' ? window.outerHeight : (document.body.clientHeight - 22),
                width = w ? w : 500,
                height = h ? h : 420,
                left = l ? l : parseInt(screenX + ((outerWidth - width) / 2), 10),
                top = t ? t : parseInt(screenY + ((outerHeight - height) / 2.5), 10);

            return (
                'width=' + width +
                ',height=' + height +
                ',left=' + left +
                ',top=' + top
            );
        }
    });

    return $.mage.amSocialLoginAjax;
});

define([
    "jquery",
    "mage/loader",
    'mage/translate',
    "Amasty_SocialLogin/js/am-reload-header",
    "Magento_Customer/js/customer-data",
    'underscore'
], function ($, loader, $translate, amReloadHeader, customerData, _) {
    'use strict';

    $.widget('mage.amLoginPopup', {
        customerData: customerData,
        options: {
            selectors: {
                login: 'a[href*="customer/account/login"]',
                logOut: 'a[href*="customer/account/logout"]',
                createAccount: 'a[href*="customer/account/create"]',
                loginOverlay: '[data-am-js="am-login-overlay"]',
                tabWrapper: '[data-am-js="am-tabs-wrapper"]',
                contentWrapper: '[data-am-js="am-content-wrapper"]',
                popup: '[data-am-js="am-login-popup"]',
                form: '[data-am-js="am-login-popup"] .form',
                default_error: '',
                error_messages: '.am-error'
            },
            redirect_duration: 2000,
            popup_duration: 300
        },

        _create: function () {
            this.init();
        },

        init: function () {
            this.initBindings();
            this.initClosePopupBindings();
            this.changeForms();
        },

        initBindings: function () {
            var self = this,
                protocol = document.location.protocol;

            $(self.options.selectors.login).prop('href', '#').on('click', function (event) {
                self.openPopup(0);
                event.preventDefault();
                return false;
            });

            /* observe create account links*/
            $(self.options.selectors.createAccount).prop('href', '#').on('click', function (event) {
                self.openPopup(1);
                event.preventDefault();
                return false;
            });

            /* checkout page*/
            $('body').addClass('amsocial-popup-observed'); //hide magento popup on checkout page
            $(document).on('click', '[data-trigger="authentication"]', function (event) {
                self.openPopup(0);
                event.preventDefault();
                return false;
            });

            $(self.options.selectors.logOut)
                .prop('href', '#')
                .removeAttr('data-post', '')
                .unbind('click')
                .on('click', function (event) {
                        self.sendLogOut();
                        event.preventDefault();
                        return false;
                    }
                );

            $(self.options.selectors.form).each(function (index, element) {
                element = $(element);
                var action = element.attr('action'),
                    parser = document.createElement('a');
                    parser.href = action;
                if (protocol !== parser.protocol) {
                    element.attr('action', action.replace(parser.protocol, protocol));
                }
            });

            $(self.options.selectors.form).unbind('submit').on('submit', function (event) {
                var element = $(this);

                self.options.selectors.default_error = $(element).parents('.am-content').find('[data-am-js="am-default-error"]');
                $(self.options.selectors.default_error).hide();
                if (element.valid()) {
                    element.find('button.action').prop('disabled', true);
                    self.sendFormWithAjax(element);
                }

                event.preventDefault();
                return false;
            });
        },

        initClosePopupBindings: function () {
            var self = this;

            $(self.options.selectors.loginOverlay).on('click', function (e) {
                var target = $(e.target);
                if (target.hasClass('am-login-overlay') || target.hasClass('am-close')) {
                    self.closePopup();
                }
            });
        },

        sendLogOut: function () {
            var self = this;
            this.showAnimation();

            $.ajax({
                url: self.options.logout_url,
                type: 'post',
                dataType: 'json',
                complete: function () {
                    self.hideAnimation();
                },
                success: function (response) {
                    if (response && response.message) {
                        self.showResultPopup(response.message);
                        if (!self._isCustomerAccountPage()) {
                            if (response.redirect === '1') {
                                response.redirect = '2';
                            }
                            amReloadHeader.customRedirect(response);
                        } else {
                            setTimeout(function () {
                                window.location.href = '/';
                            }, self.options.redirect_duration);
                        }

                    } else {
                        window.location.href = 'customer/account/logout/';
                    }
                },
                error: function () {
                    window.location.href = 'customer/account/logout/';
                }
            });

            return false;
        },

        _isCustomerAccountPage: function () {
            return $('body').hasClass('account');
        },

        showResultPopup: function (message) {
            var parent = $(this.options.selectors.contentWrapper).addClass('am-login-success').html('');

            $('<div/>').html(message).appendTo(parent);
            parent.show();
            $(this.options.selectors.tabWrapper).hide();
            $(this.options.selectors.loginOverlay).fadeIn(this.options.popup_duration);
        },

        sendFormWithAjax: function (form) {
            var self = this;
            self.form = form;
            this.showAnimation();
            $.ajax({
                url: form.attr('action'),
                data: form.serialize(),
                type: 'post',
                dataType: 'html',
                complete: function () {
                    self.hideAnimation();
                },
                success: function (response) {
                    var cookieMessages = _.unique($.cookieStorage.get('mage-messages'), 'text');
                    $.cookieStorage.set('mage-messages', '');

                    if (cookieMessages.length) {
                        var correct = true;
                        $(cookieMessages).each(function (index, message) {
                            if (message.type === 'error') {
                                correct = false;
                            }
                        });

                        if (!correct) {
                            self.showDefaultMessage(self.form, cookieMessages);
                            return;
                        }
                    }

                    if (cookieMessages.length) {
                        self.showResultPopup(cookieMessages[0].text);
                    } else {
                        if (self.form.hasClass('form-login')) {
                            if (response.indexOf('customer/account/logout') !== -1) {
                                self.showResultPopup($translate('You have successfully logged in.'));
                            } else {
                                self.showDefaultMessage(self.form);
                                return;
                            }
                        } else {
                            self.showResultPopup($translate('Thank you for registering with us.'));
                        }
                    }

                    self.headerUpdate();
                    self.form.find('button.action').removeProp('disabled');
                },
                error: function () {
                    self.showDefaultMessage(self.form);
                }
            });
        },

        headerUpdate: function () {
            var self = this;

            $.ajax({
                url: self.options.header_update,
                type: 'post',
                dataType: 'json',
                success: function (response) {
                    if (response) {
                        amReloadHeader.customRedirect(response);
                    } else {
                        setTimeout(function () {
                            window.location.reload(true);
                        }, self.options.redirect_duration);
                    }
                }
            });
        },

        openPopup: function (activeTabIndex) {
            this.showMore();
            this.refreshPopup();

            if ($('html').hasClass('nav-open')) {
                $('.navigation > .ui-menu').menu('toggle');
            }

            $(this.options.selectors.loginOverlay).fadeIn(this.options.popup_duration);
            $(this.options.selectors.popup).tabs('activate', activeTabIndex);
        },

        refreshPopup: function () {
            $(this.options.selectors.contentWrapper).hide();
            $(this.options.selectors.tabWrapper).show();
        },

        closePopup: function () {
            $(this.options.selectors.error_messages).hide();
            $(this.options.selectors.loginOverlay).fadeOut(this.options.popup_duration);
        },

        changeForms: function () {
            var parent = $(this.options.selectors.popup);
            /*Login Form*/

            /*adding placeholders for fields*/
            parent.find('.am-login-content [name="login[username]"]').prop('placeholder', $translate('Email'));
            parent.find('.am-login-content [name="login[password]"]').prop('placeholder', $translate('Password'));

            /*moving 'forgot password' link*/
            parent.find('.fieldset.login .actions-toolbar .secondary')
                .insertAfter('[data-am-js="am-login-popup"] .fieldset.login .field.password');

            /*Register Form*/

            /*moving 'newsletter' checkbox*/
            parent.find('.am-register-content .field.choice.newsletter')
                .insertBefore('[data-am-js="am-login-popup"] .am-register-content .field.password');
        },

        showDefaultMessage: function (form, messages) {
            var popup = $(this.options.selectors.popup);

            this.options.selectors.default_error = $(form).parents('.am-content').find('[data-am-js="am-default-error"]');

            if (!popup.has('.am-social-wrapper').length &&
                (popup.hasClass('-social-right') || popup.hasClass('-social-left'))) {
                $('.am-login-content').addClass('-empty');
            }

            var parent = $(this.options.selectors.default_error).html(''),
                div = null;

            if (messages) {
                $(messages).each(function (index, message) {
                    div = $('<div/>').html(message.text).appendTo(parent);
                });
            } else {
                $('<div/>').html($translate('Sorry, an unspecified error occurred. Please try again.')).appendTo(parent);

                //when we don't know error - it is better to make reload - error can be connected to form_key
                setTimeout(function () {
                    window.location.reload(true);
                }, this.options.redirect_duration);
            }

            parent.slideDown();
            form.find('button.action').removeProp('disabled');


        },

        showAnimation: function () {
            $('body').trigger('processStart');
        },

        hideAnimation: function () {
            $('body').trigger('processStop');
        },

        showMore: function () {
            var self = this,
                buttonWrap = '[data-amslogin="button-wrap"]',
                showMoreButton = 'data-amslogin="showmore"';

            $(buttonWrap).each(function () {
                if (!$(this).find('[' + showMoreButton + ']').length
                        && $(this).children().length > 3
                        && $(this).parents('.am-login-popup').length) {
                    $(this).find('.am-button-wrapper:nth-child(3)').after('<p class="amslogin-show-more"'
                        + showMoreButton + '><span class="amslogin-label">'
                        + $translate('Show More') + '<span class="amslogin-arrow"></span></span></p>');
                    $(this).find('.amslogin-show-more ~ .am-button-wrapper').hide();
                }
            });

            $('[' + showMoreButton + ']').off().on('click', function () {
                var buttons = $(this).parent().find('.am-button-wrapper');
                $(this).fadeOut(self.options.popup_duration).remove();
                buttons.fadeIn(self.options.popup_duration);
            });
        }
    });

    return $.mage.amLoginPopup;
});

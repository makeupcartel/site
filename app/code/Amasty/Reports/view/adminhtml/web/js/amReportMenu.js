define([
    'jquery',
    'collapsible',
    'matchMedia',
    'domReady!'
], function ($) {
    'use strict';

    var helpers = {
        domReady: function() {
            var accordionResize = function($accordions) {
                $accordions.forEach(function(element, index){
                    var $accordion = $(element);
                    mediaCheck({
                        media: '(min-width: 1024px)',
                        entry: function() {
                            $accordion.collapsible('activate');
                            $accordion.collapsible('option', 'collapsible', false);
                        },
                        exit: function() {
                            $accordion.collapsible('deactivate');
                            $accordion.collapsible('option', 'collapsible', true);

                            var menu = $('[data-amreports-js="menu"]'),
                                menuList = $('[data-amreports-js="menu-list"]'),
                                menuContainer = $('[data-amreports-js="menu-container"]');

                            menu.on('click', function (event) {
                                $(this).addClass('-active');
                                menuContainer.addClass('-active');
                                menuList.show();

                                if ($(event.target).hasClass('amreports-close')) {
                                    $(this).removeClass('-active');
                                    menuContainer.removeClass('-active');
                                    menuList.hide();
                                }
                            });
                        }
                    });

                    $('[data-amreports-js="content"]').off("click");
                    $('[data-amreports-js="content"]').off("keydown");
                });
            };

            var $container = $('[data-amreports-js="accordion"]'),
                $accordions = [],
                accordionOptions = {
                    collapsible: true,
                    header: '[data-amreports-js="heading"]',
                    trigger: '',
                    content: '[data-amreports-js="content"]',
                    openedState: 'active',
                    animate: false
                };

            $container.children("div").each(function(index, elem){
                var $this = $(elem),
                    $accordion = $this.collapsible(accordionOptions);

                $accordions.push($accordion);
            });

            accordionResize($accordions);

            var otherPage = $('.amreports-other-pages'),
                title = otherPage.find('.amreport-main-title'),
                additionalInfo = otherPage.find('.amreport-additional-info'),
                dashboardContainer = otherPage.find('.amreports-dashboard-container'),
                menuWrapper = otherPage.find('.menu-wrapper');

            dashboardContainer.prepend(additionalInfo);
            dashboardContainer.prepend(title);

            if (!menuWrapper.hasClass('_fixed')) {
                menuWrapper.addClass('_fixed');
            }
        }
    };
    helpers.domReady();
});

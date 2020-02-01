/*global $ */

require(['jquery'], function(jQuery) {
    jQuery(document).ready(function() {

        jQuery('.menu > ul > li:has( > ul)').addClass('menu-dropdown-icon');
        //Checks if li has sub (ul) and adds class for toggle icon - just an UI


        jQuery('.menu > ul > li > ul:not(:has(ul))').addClass('normal-sub');
        //Checks if drodown menu's li elements have anothere level (ul), if not the dropdown is shown as regular dropdown, not a mega menu (thanks Luka Kladaric)

        jQuery(".menu > ul").before("<a href=\"#\" class=\"menu-mobile\">Navigation</a>");

        //Adds menu-mobile class (for mobile toggle menu) before the normal menu
        //Mobile menu is hidden if width is more then 959px, but normal menu is displayed
        //Normal menu is hidden if width is below 959px, and jquery adds mobile menu
        //Done this way so it can be used with wordpress without any trouble

        jQuery(".menu > ul > li").hover(function(e) {
            if (jQuery(window).width() > 767) {
                var duration = '0.3s';
                if (animation_time) {
                    duration = animation_time + 's';
                }
                jQuery(this).children("ul").stop(true, false).css({ 'animation-duration': duration });
                e.preventDefault();
            }
        }, function(e) {
            if (jQuery(window).width() > 767) {
                // jQuery(this).children("ul").stop(true, false).fadeOut(150);
                e.preventDefault();
            }
        });

        jQuery(".menu-vertical-items").hover(function(e) {
            jQuery('.menu-vertical-items').removeClass('active');
            jQuery('.vertical-subcate-content').removeClass('active');
            jQuery(this).addClass('active');
            jQuery('#' + jQuery(this).data('toggle')).addClass('active');
        });
        //If width is more than 943px dropdowns are displayed on hover


        //If width is less or equal to 943px dropdowns are displayed on click (thanks Aman Jain from stackoverflow)

        jQuery(".menu-mobile").click(function(e) {
            jQuery(".menu > ul").toggleClass('show-on-mobile');
            e.preventDefault();
        });
        //when clicked on mobile-menu, normal menu is shown as a list, classic rwd menu story (thanks mwl from stackoverflow)

        /* menu toggle for mobile menu */
        var menuToogle = function() {
            if (jQuery('html').hasClass('nav-open')) {
                jQuery('html').removeClass('nav-open');
                setTimeout(function() {
                    jQuery('html').removeClass('nav-before-open');
                }, 300);
            } else {
                jQuery('html').addClass('nav-before-open');
                setTimeout(function() {
                    jQuery('html').addClass('nav-open');
                }, 42);
            }
        }
        jQuery(document).on("click", ".action.nav-toggle", menuToogle);

        /* Apply has active to parents */
        //jQuery('.nav-sections-item-content li.active:not(.menu-vertical-items)').each(function () {
        jQuery('.nav-sections-item-content li.active').each(function() {
            jQuery(this).parents('li').addClass('has-active');
            jQuery(this).addClass('has-active');

        });
        if (jQuery(window).width() >= 768) {

            jQuery('.has-active').parents('.vertical-subcate-content').addClass('active');
            jQuery('.vertical-menu-left li[data-toggle="' + jQuery('.has-active').parents('.vertical-subcate-content').attr('id') + '"]').addClass('active');
            if (jQuery('.menu-vertical-items.active').length >= 1) {
                jQuery('.menu-vertical-items.active').each(function() {
                    jQuery('#' + jQuery(this).data('toggle')).addClass('active');
                });
            }
            if (jQuery('.menu-vertical-wrapper').find('.active').length <= 0) {
                jQuery('.menu-vertical-wrapper').each(function() {
                    jQuery(this).find('.menu-vertical-items:first-child').addClass('active');
                    jQuery('#' + jQuery(this).find('.menu-vertical-items:first-child').data('toggle')).addClass('active');
                });
            }
        }
        /* Apply has active to parents */

        if (jQuery(window).width() <= 767) {
            jQuery('.col-menu-3.vertical-menu-left .menu-vertical-items').each(function() {
                var childDivId = jQuery(this).data('toggle');
                //jQuery('#'+childDivId+' ul').addClass('animated bounceIn');
                jQuery(this).append(jQuery('#' + childDivId).html());
                jQuery('.menu-vertical-items .menu-vertical-child').hide();
            });
        }
    });
});
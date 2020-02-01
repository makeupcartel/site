require([
    'jquery',
    'owlCarousel',
    'rangeslider',
    'parally'
], function ($) {
    jQuery(document).ready(function () {

        setTimeout(function () {
            var height = $('.page-products .products.products-grid .product-items .product-item').height();
            $(".page-products .products.products-grid .category-cms").css("cssText", "height: " + height + "px;");
            window.dispatchEvent(new Event('resize'));
        }, 2000);


        //checkout button edit shipping field
        $(document).on('click', '.edit-field-btn', function() {
            var id = $(this).data('id');
            if($('#'+id).hasClass('locked')){
                $('#'+id).removeClass('locked');
                $('label.locked[for="'+id+'"]').removeClass('locked');
                $('#'+id).focus();
            }else{
                $('#'+id).focus();
            }
        });

        jQuery('.decreaseQtybundle').click(function (event) {
            var qty = parseInt(jQuery(this).closest('.control').find(".qty").val()) - 1;
            if (qty <= 0) {

            } else jQuery(this).closest('.control').find('.qty').val(qty);

        });
        jQuery('.increaseQtybundle').click(function (event) {
            var qty = parseInt(jQuery(this).closest('.control').find(".qty").val()) + 1;
            jQuery(this).closest('.control').find('.qty').val(qty);
        });

        // remove,and apperance search in header when click

        $('.block.block-search .control span.close-search').click(function () {
            $('.block.block-content form#search_mini_form .control').css('display', 'none');
        });

        $('.block-search .form.minisearch .field.search label.label').click(function () {
            $('.block.block-content form#search_mini_form .control').css('display', 'block');
        });

        $(document).click(function (e) {
            var container = $("input#search");
            var checkvalue = $("input#search").val();
            var checkclass = $(".amasty-xsearch-loader").hasClass('amasty-xsearch-hide');
            if (!container.is(e.target) && container.has(e.target).length === 0) {
                if (checkvalue == '' || checkvalue == null) {
                    if (checkclass == true) {
                        $("form#search_mini_form .actions").css('display', 'block');
                    }
                }
            }
        });


        // end remove search in header when click


        /* cart qty in cart page */
        $('.btn_I_or_D').click(function () {
            if ($(this).hasClass('bt-increase')) {
                var qty = parseInt(jQuery(this).closest('.control').find('input.qty').val()) - 1;
                if (qty > 0) {
                    jQuery(this).closest('.control').find('input.qty').val(qty);
                    $('.cart.main.actions .action.update').trigger('click');
                }
            } else {
                var qty = parseInt(jQuery(this).closest('.control').find('input.qty').val()) + 1;
                jQuery(this).closest('.control').find('input.qty').val(qty);
                $('.cart.main.actions .action.update').trigger('click');
            }
        });
        $('.checkout-cart-index .cart.table-wrapper .col.qty input.input-text').on('change', function () {
            if ($('.cart.main.actions .action.update').length) {
                $('.cart.main.actions .action.update').trigger('click');
            }
        });
        $("span.edit_month").click(function () {
            var valueOption = $(this).closest("tbody.cart.item").find('.additional-info-wrapper').length;
            if (valueOption != 0) {
                var widthScreen = $(window).width();
                if (widthScreen >= 768) {
                    $(this).closest("tbody.cart.item").find('.additional-info-wrapper').css("display", "table-row");
                } else {
                    $(this).closest("tbody.cart.item").find('.additional-info-wrapper').css("display", "inline-flex");
                }
            }
        });
        $(".update_product_cart").click(function () {
            $('.cart.main.actions .action.update').trigger('click');
        });
        /* CLICK OUTSIDE CONFIRM BOX AJAX ADD TO CART */
        $(document).on('click', '.confirmBox-wrapper', function (e) {
            var container = $('#confirmBox');
            // If the target of the click isn't the container
            if (!container.is(e.target) && container.has(e.target).length === 0) {
                $('.confirmBox-wrapper').remove();
                $('#confirmOverlay').remove();
            }
        });
        /* READ MORE BTN ON PRODUCT PAGE SHORT DESCRIPTION */
        $('.readmore-short-desc').click(function (e) {
            e.preventDefault();
            $('.sub-short-desc').hide();
            $('.full-short-desc').show();

        });

        $('.lessmore-short-desc').click(function (e) {
            e.preventDefault();
            $('.sub-short-desc').show();
            $('.full-short-desc').hide();

        });

        //validate stress adress
        $('.street-adress').keyup(function () {
            var stressval = $.trim(this.value);
            if (stressval.length > 40) {
                $('.message-validate-stress-adress').html("Please enter less or equal than 40 characters");
                $('.message-validate-stress-adress').css({'display': 'block'});
                $("button.action.save.primary").attr("disabled", true);
                $('.street-adress').css({'border': 'solid 1px red'});
            } else {
                $('.message-validate-stress-adress').css({'display': 'none'});
                $("button.action.save.primary").attr("disabled", false);
                $('.street-adress').css({'border': 'solid 1px #e4e4e4'});
            }
        });

        $('.tel-account').keyup(function () {
            var x = $.trim(this.value);
            if (x.length < 7) {
                $('.message-validate-phone').html("Please enter more or equal than 7 characters");
                $('.message-validate-phone').css({'display': 'block'});
                $("button.action.save.primary").attr("disabled", true);
                $('.tel-account').css({'border-color': 'red'});
            }else 
            if (validateTelephoneAcocount(x) === 0) {
                $('.message-validate-phone').html("Please enter a valid number in this field.");
                $('.message-validate-phone').css({'display': 'block'});
                $("button.action.save.primary").attr("disabled", true);
                $('.tel-account').css({'border-color': 'red'});
            }else {
                $('.message-validate-phone').css({'display': 'none'});
                $("button.action.save.primary").attr("disabled", false);
                $('.tel-account').css({'border-color': '#e4e4e4'});
            }
        });

        function validateTelephoneAcocount(telephone) 
        {
            var filter = /^[0-9]*$/g;
            if (filter.test(telephone)) 
            {
                return 1;
            } else {
                return 0;
            }
        }

        // click edit items cart
        $(document).on('click', '.minicart-items .product .product-item-details .action.edit', function (e) {
            var href = $(this).attr('href');
            if (href && href != '') {
                e.preventDefault();
                window.location.href = href;
            }
        });
        setInterval(function selectField() {
            jQuery('.account.magento_rma-returns-create .fields.additional .field:not(.item) select option:first-of-type').text('Please select');
            // jQuery('.account.magento_rma-returns-create .fields.additional .field:not(.item) select option:first-of-type').attr('value','0');
            var requiredSelect = jQuery('.account.magento_rma-returns-create .fields.additional .field.required:not(.item)');
            requiredSelect.find('select').attr('required', 'required');

            jQuery('.account.magento_rma-returns-create .fields.additional .field:not(.item) input').change(function () {
                var valueInput = jQuery(this).val();
                if (valueInput <= 0) {
                    jQuery(this).addClass('mage-error');
                    jQuery(this).next().show();
                } else {
                    jQuery(this).removeClass('mage-error');
                    jQuery(this).next().hide();
                }
            })
            if (jQuery('html .block-search .control .close-search').length) {
                jQuery('html .block-search .control .close-search').click(function () {
                    jQuery('.page-wrapper .page-header .header.content .block-search .form.minisearch .field.search .label').removeClass('active');
                })
            }

            jQuery('.account.magento_rma-returns-create .fields.additional .field:not(.item) select').change(function () {
                var valueSelected = jQuery(this).find('option:selected').val();
                if (valueSelected) {
                    jQuery(this).removeClass('mage-error');
                    jQuery(this).next().hide();
                } else {
                    jQuery(this).addClass('mage-error');
                    jQuery(this).next().show();
                }
            });

        }, 2000);


        /* NEXT STEP SKIN QUIZ */


        function validateEmail(sEmail) {
            var filter = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            if (filter.test(sEmail)) {
                return true;
            } else {
                return false;
            }
        }


        $(".secondary-begin-quiz").click(function () {

            if ($("select.age-quiz option:checked").val() === '' || $("input#emailquiz").val() === '') {

                if ($("input#emailquiz").val() === '') {
                    $('.message-error-quiz-email').html('This is a required field.');
                    $('.message-error-quiz-email').css({'color': 'red', 'margin-top': '5px'});
                    $('input#emailquiz').css({'border': '1px solid red'});
                }
                if ($("select.age-quiz option:checked").val() === '') {
                    $('.message-error-quiz').html('*This is a required field.');
                    $('.message-error-quiz').css({'color': 'red', 'margin-top': '5px'});
                    $('.age-quiz').css({'border': '1px solid red'});
                }
            } else {
                var semail = $("input#emailquiz").val();
                if (validateEmail(semail)) {
                    $(".st-1").css({'border': '2px #49C1E0 solid'});
                    $(".st-1>p").css({'color': '#49C1E0!important'});
                    $(".tick-1>p").css({'color': '#49C1E0!important'});
                    // $(".tick-1").addClass('active-step');
                    $(".get-name-age").css({'display': 'none'});
                    $(".step-2").css({'display': 'block'});
                    $('html, body').animate({scrollTop: 0}, '300');
                    $(".tick-1").addClass('tick-1-config');
                } else {
                    $('.message-error-quiz-email').html('Please enter a valid email address (Ex: johndoe@domain.com).');
                    $('.message-error-quiz-email').css({'color': 'red', 'margin-top': '5px'});
                    $('input#emailquiz').css({'border': '1px solid red'});
                }

            }
        });


        $("#btn-nextstep-1").click(function () {

            $('html, body').animate({scrollTop: 0}, '300');
        });
        $("#btn-nextstep-2").click(function () {

            $(".st-1").css({'border': '1px #49C1E0 solid', 'background-color': '#49C1E0', 'color': '#ffffff'});
            $(".st-1>p").css({'display': 'none'});
            $(".line-1").css({'background-color': '#49C1E0'});
            $(".icon_check_1").css({'display': 'block'});
            $(".st-2").css({'border': '2px #49C1E0 solid'});
            $(".st-2>p").css({'color': '#49C1E0'});
            $(".tick-2").css({'color': '#49C1E0'});
            $(".step-2").css({'display': 'none'});
            $(".step-3").css({'display': 'block'});
            $(".tick-2").addClass('tick-2-config');
            $('html, body').animate({scrollTop: 0}, '300');
        });

        $("#btn-nextstep-3").click(function () {
            $(".step-3").css({'display': 'none'});
            $(".step-1").css({'display': 'block'});
            $(".st-2").css({'border': '1px #49C1E0 solid', 'background-color': '#49C1E0', 'color': '#ffffff'});
            $(".st-2>p").css({'display': 'none'});
            $(".st-3").css({'border': '2px #49C1E0 solid'});
            $(".line-2").css({'background-color': '#49C1E0'});
            $(".icon_check_2").css({'display': 'block'});
            $(".st-3>p").css({'color': '#49C1E0'});
            $(".tick-3").css({'color': '#49C1E0'});
            $('html, body').animate({scrollTop: 0}, '300');
            $(".tick-3").addClass('tick-3-config');
        });


        $(".goback-get-name-age").click(function () {
            $(".step-2").css({'display': 'none'});
            $(".get-name-age").css({'display': 'block'});
            $(".line-1").css({'background-color': '#999999'});
            $(".st-1").css({'border': '2px #999999 solid'});
            $(".st-1>p").css({'color': '#999999'});
            $(".tick-1").css({'color': '#999999'});
            $(".tick-1").removeClass('tick-1-config');
        });

        $(".goback-step-2").click(function () {
            $(".step-1").css({'display': 'none'});
            $(".step-3").css({'display': 'block'});
            $(".line-2").css({'background-color': '#999999'});
            $(".st-3").css({'border': '2px #c7c7c7 solid', 'color': '#999999'});
            $(".st-3>p").css({'color': '#333333'});
            $(".tick-3").css({'color': '#999999'});
            $(".st-2").css({'background-color': '#ffffff', 'color': '#999999', 'border': '2px #49c1df solid'});
            $(".st-2>p").css({'display': 'block'});
            $(".st-2>span").css({'display': 'none'});
            $(".tick-2").css({'color': '#49C1E0'});
            $("img.icon_check_quiz.icon_check_2").css({'display': 'none'});
            $(".tick-3").removeClass('tick-3-config');
        });

        $(".goback-step-1").click(function () {
            $(".step-3").css({'display': 'none'});
            $(".step-2").css({'display': 'block'});
            $(".line-1").css({'background-color': '#999999'});
            $(".line-2").css({'background-color': '#999999'});
            $(".st-2").css({'border': '2px #c7c7c7 solid'});
            $(".st-2>p").css({'color': '#333333'});
            $(".tick-2").css({'color': '#999999'});
            $(".st-1").css({'background-color': '#ffffff', 'color': '#49c0e0', 'border': '2px #49c0e0 solid'});
            $(".st-1>p").css({'display': 'block', 'color': '#49c0e0'});
            $(".st-1>span").css({'display': 'none'});
            $("img.icon_check_quiz.icon_check_1").css({'display': 'none'});
            $(".tick-2").removeClass('tick-2-config');
        });


        /* mobile skin quiz step-3*/
        var $btnshowslider = $('.show-slider-rate');
        var $urlicon = $('.url-icon-show-hidden').val();
        var $srcicon = '';
        $srcicon += '<img src="';
        $srcicon += $urlicon;
        $srcicon += '">';
        $btnshowslider.click(function () {
            if ($(this).html() === '+') {
                $(this).html($srcicon);
                $(this).closest('.sentence-concern').css('overflow', 'unset');
                $(this).closest('.sentence-concern').attr('style', 'height: auto !important');
            } else {
                $(this).html('+');
                $(this).closest('.sentence-concern').css({'height': '61px', 'overflow': 'hidden'});
            }
        });
        /* END NEXT STEP SKIN QUIZ */

        /* ACCOUNT */
        $(".icon-account").click(function () {

            $(".submenu.content").slideToggle();
            var sttdisplay = $('.submenu.content').css("display");

        });

        $(document).mouseup(function (e) {
            var container = $(".header-account");
            // If the target of the click isn't the container
            if (!container.is(e.target) && container.has(e.target).length === 0) {
                container.find('.submenu.content').slideUp();
            }
        });

        $(".action-share").click(function () {
            var linkshare = window.location.href;
            $('#link-share-cus').val(linkshare);
        });
        jQuery('.bg-sticky').parally({speed: 0.4});

        /*Height Image Homepage*/
        setTimeout(function () {
            if (jQuery('.block.block-products-list').length) {
                jQuery('.block.block-products-list .product-item').each(function () {
                    var width_image = jQuery(this).outerWidth();
                    jQuery(this).find('img').css("cssText", "max-height: " + width_image + "px;");
                })
            }
        }, 1500);
        jQuery(window).resize(function () {

         var windowWidth = $(window).width();
         var windowHeight = $(window).height();
            setTimeout(function () {
                if (jQuery('.block.block-products-list').length) {
                    jQuery('.block.block-products-list .product-item').each(function () {
                        var width_image = jQuery(this).outerWidth();
                        jQuery(this).find('img').css("cssText", "max-height: " + width_image + "px;");
                    })
                }
            }, 1500);
            if (windowWidth <= 767) {
                jQuery('#layered-filter-block .filter-title strong').text('Filter');
            }
        });
    });
    /* END ACCOUNT */
    /*dropdown menu website blog*/


    $(".toggle-menu-responsive").click(function () {
        $(".block-category-listing").toggle();
    });

    jQuery("#switcher-currency-trigger-nav").click(function () {
        var checklanguage = jQuery('.actions.dropdown.options.switcher-options').hasClass('active');
        var checkCart = jQuery('.action.showcart').hasClass('active');
        if ((checklanguage == false && checkCart == true) || checkCart == true) {
            jQuery('.minicart-wrapper').removeClass("active");
            jQuery('.action.showcart.active').removeClass("active");
            jQuery('.ui-dialog.ui-widget.ui-widget-content.ui-corner-all.ui-front.mage-dropdown-dialog').css("display", "none");
            jQuery('.actions.dropdown.options.switcher-options').addClass("active");
            jQuery('.action.toggle.switcher-trigger').addClass("active");
        }
    });


    jQuery("a.action.showcart").click(function () {
        var checklanguage = jQuery('.actions.dropdown.options.switcher-options').hasClass('active');
        var checkCart = jQuery('.action.showcart').hasClass('active');
        if ((checklanguage == true && checkCart == false) || (checklanguage == true)) {
            jQuery('.actions.dropdown.options.switcher-options').removeClass("active");
            jQuery('.action.toggle.switcher-trigger').removeClass("active");
            jQuery('.ui-dialog.ui-widget.ui-widget-content.ui-corner-all.ui-front.mage-dropdown-dialog').css("display", "block");
            jQuery('.minicart-wrapper').addClass("active");
            jQuery('.action.showcart.active').addClass("active");
        }
    });


    /* ADD CLASS FIRST WORD TO CHECKOUT PAGE*/
    if (jQuery('.checkout-index-index').length) {
        var addclass_first_word_checkout = setInterval(function () {
            if (jQuery('.payment-group').length) {
                jQuery(".title-number").remove();
                jQuery(".step-title").html(function () {
                    if (jQuery(this).text().indexOf(" ") >= 0) {
                        var text = jQuery(this).text().trim().split(" ");
                        var first = text.shift();
                        return (text.length > 0 ? "<span class='first-word'>" + first + "</span> " : first) + text.join(" ");
                    } else {
                        return "<span class='first-word'>" + jQuery(this).text() + "</span> "
                    }
                });
                clearInterval(addclass_first_word_checkout);
            }
        }, 2000);
    }

    /* COMMON FUNCTION ALIGN HEIGHT */
    var width = jQuery(window).width();

    // Mega menu for ipad

    if (width < 1200 && width >= 768) {
        jQuery('.nav-sections-items .section-item-content .menu-container .menu.horizontal > ul > li.mega-v01a > a , .nav-sections-items .section-item-content .menu-container .menu.horizontal>ul>li.mega-v01b > a ').click(function () {
            event.preventDefault();
            jQuery(this).closest('li').find('.column1').slideToggle();
            jQuery(this).parent().siblings().find('.column1').hide();
        })
    }

    if (width < 768) {
        if ($('.checkout-cart-index .form-cart .product-image-container img.product-image-photo').length) {
            var img = $('.checkout-cart-index .form-cart .product-image-container img.product-image-photo');
            img.each(function () {
                var height = $(this).height();
                $(this).closest('.item').css('min-height', height + 28);
            })
        }
        jQuery('.section-item-content .menu-container .menu>ul>li.mega-v01a > a ,.section-item-content .menu-container .menu>ul>li.mega-v01b > a').click(function () {
            event.preventDefault();
            jQuery(this).toggleClass('active');
           /* console.log(jQuery(this).closest('li').find('.column1'));*/
            jQuery(this).closest('li').find('.column1').slideToggle();
            jQuery(this).parent().siblings().find('.column1').hide();

        })
        var countLi = jQuery('.section-item-content .menu-container .menu>ul>li');
        if (countLi.length % 2 == 0) {
            jQuery('.section-item-content .menu-container .menu>ul').append('<li></li>');
        }
    }

    function alignHeight(selector) {
        jQuery(selector).css('height', '');
        var minHeight = 0;
        jQuery(selector).each(function () {
            if (minHeight < jQuery(this).outerHeight()) {
                minHeight = jQuery(this).outerHeight();
            }
        });
        if (minHeight > 0) {
            jQuery(selector).css('height', minHeight);
        }
    }

    /*ALIGN HEIGHT PRODUCT GRID HOMEPAGE*/
    setTimeout(function () {
        alignHeight('.cms-index-index .products-grid .product-item .product-item-info .align-summary');
        alignHeight('.cms-index-index .products-grid .product-item .product-item-info .product-item-name .product-item-link');
        alignHeight('.cms-index-index .products-grid .product-item .product-item-info .price-box');
        alignHeight('.cms-index-index .products-grid .product-item .product-item-info .align-swatch');
        alignHeight('.account.wishlist-index-index .products-grid.wishlist .product-item .product-item-info .product-item-name .product-item-link');
        alignHeight('.account.wishlist-index-index .products-grid.wishlist .product-item .product-item-info .price-box');
        alignHeight('.aw_blog-post-view .products-grid .product-item .product-item-info .align-summary');
        alignHeight('.aw_blog-post-view .products-grid .product-item .product-item-info .product-item-name .product-item-link');
        alignHeight('.aw_blog-post-view .products-grid .product-item .product-item-info .price-box');
        alignHeight('.aw_blog-post-view .products-grid .product-item .product-item-info .align-swatch');
        alignHeight('.product-item .align-attribute-new');

        alignHeight('.checkout-cart-index .crosssell .product-item-name');
        alignHeight('.checkout-cart-index .crosssell .align-summary');
        alignHeight('.checkout-cart-index .crosssell .price-box');
        alignHeight('.checkout-cart-index .crosssell .align-swatch');
        alignHeight('.checkout-cart-index .crosssell .product-item-actions');

        alignHeight('.cms-page-view .ing-box');
    }, 1000);
    jQuery(window).resize(function () {
        setTimeout(function () {
            alignHeight('.cms-index-index .products-grid .product-item .product-item-info .align-summary');
            alignHeight('.cms-index-index .products-grid .product-item .product-item-info .product-item-name .product-item-link');
            alignHeight('.cms-index-index .products-grid .product-item .product-item-info .price-box');
            alignHeight('.cms-index-index .products-grid .product-item .product-item-info .align-swatch');
            alignHeight('.account.wishlist-index-index .products-grid.wishlist .product-item .product-item-info .product-item-name .product-item-link');
            alignHeight('.account.wishlist-index-index .products-grid.wishlist .product-item .product-item-info .price-box');
            alignHeight('.aw_blog-post-view .products-grid .product-item .product-item-info .align-summary');
            alignHeight('.aw_blog-post-view .products-grid .product-item .product-item-info .product-item-name .product-item-link');
            alignHeight('.aw_blog-post-view .products-grid .product-item .product-item-info .price-box');
            alignHeight('.aw_blog-post-view .products-grid .product-item .product-item-info .align-swatch');
            alignHeight('.product-item .align-attribute-new');

            alignHeight('.checkout-cart-index .crosssell .product-item-name');
            alignHeight('.checkout-cart-index .crosssell .align-summary');
            alignHeight('.checkout-cart-index .crosssell .price-box');
            alignHeight('.checkout-cart-index .crosssell .align-swatch');
            alignHeight('.checkout-cart-index .crosssell .product-item-actions');

            alignHeight('.cms-page-view .ing-box');
        }, 500);
    });


    /* ADD CLASS FIRST WORD TO PAGE TITLE */
    $("h1.page-title > span").html(function () {
        if ($(this).text().indexOf(" ") >= 0) {
            var text = $(this).text().trim().split(" ");
            var first = text.shift();
            return (text.length > 0 ? "<span class='first-word'>" + first + "</span> " : first) + text.join(" ");
        } else {
            return "<span class='first-word'>" + $(this).text() + "</span> "
        }
    });

    //faq function click
    $('.content-faq .QandA .answer').hide();
    // $('.content-faq .QandA:first-of-type .answer').show();
    // $('.content-faq .QandA:first-of-type h4').addClass('active');
    $('.content-faq .QandA h4').click(function () {
        $(this).next().slideToggle();
        $(this).toggleClass('active');
        $(this).parent().siblings().find('.answer').slideUp();
        $(this).parent().siblings().find('.question').removeClass('active');
    })

    function checkgiftcontent() {
        var text = jQuery('.checkout-cart-index #giftcard-balance-lookup').text();
        if (!text) {
            jQuery('.checkout-cart-index #giftcard-balance-lookup').css('margin-bottom', '0');
        } else {
            jQuery('.checkout-cart-index #giftcard-balance-lookup').css('margin-bottom', '30px');
        }
    }

    function checkshipping() {
        var shipping = jQuery('.checkout-cart-index .page-main .columns .cart-container .cart-summary .cart-totals .shipping');
        if (shipping.length == 0) {
            jQuery('.checkout-cart-index .page-main .columns .cart-container .cart-summary .cart-totals .totals th , .checkout-cart-index .page-main .columns .cart-container .cart-summary .cart-totals .totals td').css('padding-bottom', '0px');
        } else {
            jQuery('.checkout-cart-index .page-main .columns .cart-container .cart-summary .cart-totals .totals th , .checkout-cart-index .page-main .columns .cart-container .cart-summary .cart-totals .totals td').css('padding-bottom', '10px');
        }
    }

    // setInterval(checkshipping , 10000);
    setInterval(checkgiftcontent, 10000);

    //sidebar cms page
    if ($('body').find('.sidebar-cms').length > 0) {
        var title = globalTitle.toLowerCase();
        $('#' + title).addClass('current');
        var titleShowMobile = '<div class="current-page">' + titlePageCms + '</div>';
        $('.sidebar-cms').prepend(titleShowMobile);
        $('.current-page').click(function () {
            $(this).toggleClass('active');
            $(this).closest('.sidebar-cms').find('.content').slideToggle();
        });
    }

    /* CATEGORY BANNER IMAGE */
    if (jQuery('.category-top-wrapper .category-image').length) {
        jQuery('.category-top-wrapper').addClass('has-image');
    } else {
        jQuery('.category-top-wrapper').show();
    }
    if (jQuery('.category-top-wrapper').hasClass('has-image')) {
        var background_url = jQuery('.category-image img').attr('src');
        jQuery('.category-image').css('background-image', 'url(' + background_url + ')');
        jQuery('.category-image img').hide();
        jQuery('.category-top-wrapper').show();
    }

    /* ALIGN HEIGHT PRODUCT GRID */
    setTimeout(function () {
        alignHeight('.products-grid .product-item .product-item-detail');
    }, 500);
    jQuery(window).resize(function () {
        setTimeout(function () {
            alignHeight('.products-grid .product-item .product-item-detail');
        }, 500);
    });

    /* MOVE PROMO CMS BLOCK ON CATEGORY */
    var width = jQuery(window).width();
    if (width >= 768) {
        if (jQuery('body').hasClass('catalog-category-view') && jQuery(".catalog-category-view .category-cms").length) {
            jQuery(".catalog-category-view .category-cms").insertAfter(".catalog-category-view .products.wrapper.grid.products-grid > ol > li:nth-child(4)");
        }
    } else {
        if (jQuery('body').hasClass('catalog-category-view') && jQuery(".catalog-category-view .category-cms").length) {
            jQuery(".catalog-category-view .category-cms").insertAfter(".catalog-category-view .products.wrapper.grid.products-grid > ol > li:nth-child(2)");
        }
    }

    jQuery(window).resize(function () {
        var width = jQuery(window).width();
        if (width >= 768) {
            if (jQuery('body').hasClass('catalog-category-view') && jQuery(".catalog-category-view .category-cms").length) {
                jQuery(".catalog-category-view .category-cms").insertAfter(".catalog-category-view .products.wrapper.grid.products-grid > ol > li:nth-child(4)");
            }
        } else {
            if (jQuery('body').hasClass('catalog-category-view') && jQuery(".catalog-category-view .category-cms").length) {
                jQuery(".catalog-category-view .category-cms").insertAfter(".catalog-category-view .products.wrapper.grid.products-grid > ol > li:nth-child(2)");
            }
        }
    });


    /* SET HEIGH CMS CATEGORY */
    var width = jQuery(window).width();
    if (width > 767) {
        setTimeout(function () {
            if (jQuery(".catalog-category-view .category-cms").length != 0) {
                alignHeight('.page-products .products.products-grid .product-items .product-item .product-item-details');
                alignHeight('.every-love .every-content .block-content .products-grid .product-item .product-item-info .product-item-details');
                if (width > 767 || width < 1024) {
                    promoHeight = jQuery('.catalog-category-view .products.wrapper.grid.products-grid > ol > li').outerHeight() + 0.6;
                } else {
                    promoHeight = jQuery('.catalog-category-view .products.wrapper.grid.products-grid > ol > li').outerHeight() + 0.13;
                }

                /*jQuery('.catalog-category-view .category-cms').css('height', promoHeight);*/

            }
        }, 500);
    } else {
        jQuery('.catalog-category-view .category-cms').css('height', 'auto');
    }

    /* jQuery(window).resize(function () {
         if (width > 767) {
             setTimeout(function () {
                 if (jQuery(".catalog-category-view .category-cms").length != 0) {
                     alignHeight('.page-products .products.products-grid .product-items .product-item .product-item-details');
                     alignHeight('.every-love .every-content .block-content .products-grid .product-item .product-item-info .product-item-details');
                     if (width > 767 || width < 1024) {
                         promoHeight = jQuery('.catalog-category-view .products.wrapper.grid.products-grid > ol > li').outerHeight() + 0.6;
                     } else {
                         promoHeight = jQuery('.catalog-category-view .products.wrapper.grid.products-grid > ol > li').outerHeight() + 0.13;
                     }

                     jQuery('.catalog-category-view .category-cms').css('height', promoHeight);

                 }
             }, 500);
         } else {
             jQuery('.catalog-category-view .category-cms').css('height', 'auto');
         }
     });
 */
    /*ADD QTY MY FAVOURITE*/
    jQuery('.minus_wishlist').click(function (event) {
        var qty = parseInt(jQuery(this).closest('.control').find(".qty").val()) - 1;
        if (qty <= 0) {

        } else jQuery(this).closest('.control').find('.qty').val(qty);

    });
    jQuery('.plus_wishlist').click(function (event) {
        var qty = parseInt(jQuery(this).closest('.control').find(".qty").val()) + 1;
        jQuery(this).closest('.control').find('.qty').val(qty);
    });
    var width = jQuery(window).width();
    if (width < 768) {
        /* Click Search Mobile */
        jQuery('.page-header .header.content .block-search .block-title').click(function () {
            jQuery(this).next().slideToggle();
        });
        jQuery('.page-header .block-search #algolia-searchbox .magnifying-glass').click(function () {
            jQuery(".page-header .header.content .header-center .block-search .block.block-content").slideToggle();
        });
        /*click sidebar my account mobile*/
        if (jQuery('.sidebar-main .block-collapsible-nav').length) {
            var text = jQuery('.sidebar-main .block-collapsible-nav .block-collapsible-nav-content .nav.item.current strong').text();

            jQuery('.sidebar-main .block-collapsible-nav .block-collapsible-nav-title strong').text(text);
            if (jQuery('.wishlist-index-share').length) {
                jQuery('.sidebar-main .block-collapsible-nav .block-collapsible-nav-title strong').text('My Favourites');
            }
            if (jQuery('.magento_rma-returns-view').length) {
                jQuery('.sidebar-main .block-collapsible-nav .block-collapsible-nav-title strong').text('My Return');
            }
        }
        jQuery('.account .page-title-wrapper').insertBefore(jQuery('.account .sidebar-main'));
    }
    /*MAKE SLIDE HEADER MOBILE*/

    if (width < 768) {
        jQuery('.page-wrapper .header-bottom ul').addClass('owl-carousel');
        jQuery('.page-wrapper .header-bottom ul').owlCarousel({
            margin: 0,
            items: 1,
            loop: true,
            nav: false,
            dots: false,
            autoplay: true
        });
    } else {
        jQuery(".page-wrapper .header-bottom ul").removeClass('owl-carousel');
    }
    /* ADD CLASS FIRST WORD TO WIDGET TITLE */
    var addclass_title_recently_view = setInterval(function () {
        if (jQuery('.widget .block-title > strong').length) {
            $(".widget .block-title > strong").html(function () {
                if ($(this).text().indexOf(" ") >= 0) {
                    var text = $(this).text().trim().split(" ");
                    var first = text.shift();
                    return (text.length > 0 ? "<span class='first-word'>" + first + "</span> " : first) + text.join(" ");
                } else {
                    return "<span class='first-word'>" + $(this).text() + "</span> "
                }
            });
            clearInterval(addclass_title_recently_view);
        }
    }, 2000);

    /* CATEGORY RECENTLY VIEWED SLIDE */
    var load_recently_view = setInterval(function () {
        if (jQuery('.widget.block-viewed-products-grid .product-items').length) {
            jQuery('.every-love').hide();
            jQuery('.widget.block-viewed-products-grid .product-items').addClass('owl-carousel');
            jQuery('.widget.block-viewed-products-grid .product-items.owl-carousel').owlCarousel({
                loop: false,
                dots: true,
                margin: 24,
                nav: true,
                responsiveClass: true,
                responsive: {
                    0: {
                        items: 1,
                        stagePadding: 50,
                        autoWidth: true,
                        loop: false
                    },
                    768: {
                        items: 3,
                        loop: false
                    },
                    1000: {
                        items: 4,
                        loop: false
                    }
                }
            })
            clearInterval(load_recently_view);
            jQuery('.catalog-category-view .page-wrapper .page-main .admin__data-grid-outer-wrap').show();
        } else {
            jQuery('.catalog-category-view .page-wrapper .page-main .admin__data-grid-outer-wrap').hide();
        }
    }, 2000);

    /* FILTER ITEM INGREDIENTS PAGE CMS */
    jQuery(".filter-item").each(function (i) {
        jQuery(this).click(function () {
            jQuery(this).addClass('selected').siblings().removeClass('selected');
            var dataFilter = jQuery(this).attr('data-filter');
            var arrFilter = dataFilter.split(',');
            jQuery('.ing-list .ing-item').each(function () {
                var results = jQuery(this).find('.fil-tit').text();
                var firstKey = results.toLowerCase().charAt(0);
                if (arrFilter.includes(firstKey)) {
                    jQuery(this).show();
                    alignHeight('.cms-page-view .ing-box');
                } else if (dataFilter == 'all') {
                    jQuery(this).show();
                    alignHeight('.cms-page-view .ing-box');
                } else {
                    jQuery(this).hide();
                }
            });
        });
    });

    /* STICKY FILTER MENU INGREDIENTS */
    if (jQuery(".filter-ing").length != 0) {
        var stickyNavTop = jQuery('.filter-ing').offset().top;
        var stickyNav = function () {
            var scrollTop = jQuery(window).scrollTop();
            if (scrollTop > stickyNavTop) {
                jQuery('.filter-ing').addClass('sticky');
            } else {
                jQuery('.filter-ing').removeClass('sticky');
            }
        };
        stickyNav();
        jQuery(window).scroll(function () {
            stickyNav();
        });
    }

    if (jQuery('.aw_blog-post-view .block.aw_blog_related_products')) {
        jQuery('.aw_blog-post-view .block.aw_blog_related_products').appendTo('.aw_blog-post-view .columns');
    }


    $(document).ready(function () {
        /* ERROR BORDER ON PRODUCT QTY */
        $('.catalog-product-view .box-tocart #qty').on('change', function () {
            var self = $(this);
            setTimeout(function () {
                if (self.hasClass('mage-error')) {
                    self.parent().addClass('error');
                } else {
                    self.parent().removeClass('error');
                }
            }, 200);
        });

        /* ALIGN MIDDLE VIEW ALL RESULT LINK ON PRODUCT PAGE */
        if ($('.catalog-product-view .reviews-and-links .view-real-results').length) {
            var checkReviewInterval = setInterval(function () {
                if ($('.catalog-product-view .reviews-and-links .stamped-product-reviews-badge .stamped-badge').length) {
                    var rating = $('.catalog-product-view .reviews-and-links .stamped-product-reviews-badge .stamped-badge').data('rating');
                    if (rating == '0.0') {
                        $('.catalog-product-view .reviews-and-links .stamped-product-reviews-badge').addClass('rating-0');
                    }
                    clearInterval(checkReviewInterval);
                }
            }, 500);
        }
    });
});

require(['jquery', 'aos'], function ($, AOS) {
    AOS.init({
        duration: 1200,
    });
});
require([
    'jquery'
], function (jQuery) {
    (function ($) {
        $(document).ready(function () {

            if (jQuery('.aw_blog-post-view .block.aw_blog_related_products')) {
                jQuery('.aw_blog-post-view .block.aw_blog_related_products').appendTo('.aw_blog-post-view .columns');
            }

            $(".shipping-return .title").click(function () {
                $('.shipping-return .content').slideToggle();
                $(this).toggleClass('show hide');
            });
            $(".how-to-use .title").click(function () {
                $('.how-to-use .content').slideToggle();
                $(this).toggleClass('show hide');
            });
            $(".ingredient .title").click(function () {
                $('.ingredient .content').slideToggle();
                $(this).toggleClass('show hide');
            });
            $(".description .title").click(function () {
                $('.description .content').slideToggle();
                $(this).toggleClass('show hide');
            });

            $('.readmore-short-desc').click(function (e) {
                e.preventDefault();
                $('.sub-short-desc').hide();
                $('.full-short-desc').show();

            });

            /* STICKY ADD TO CART BUTTON ON MOBILE PRODUCT PAGE */
            if ($('.product-options-bottom').length) {
                $(window).scroll(function () {
                    var width = $(window).width();
                    if (width < 768) {
                        var scrollTop = $(window).scrollTop();
                        var stickyNavTop = $('.product-options-bottom').offset().top + $('.product-options-bottom').height();

                        if (scrollTop > stickyNavTop) {
                            $('.sticky-buttons').addClass('sticky');
                        } else {
                            $('.sticky-buttons').removeClass('sticky');
                        }
                    }
                });
            }

            if ($('.product-info-main .product-add-form form #product-addtocart-button').length) {
                var text = $('.product-mobile-title .sticky-buttons .sticky-atc-button').data('text');
                var price = $('.product-options-bottom .price-box.price-final_price [data-price-type="finalPrice"] .price').text();
                if (price) {
                    $('.product-mobile-title .sticky-buttons .sticky-atc-button').text(text + ' - ' + price);
                }
            }
            $('.sticky-buttons .sticky-atc-button').click(function (e) {
                e.preventDefault();
                $('.product-info-main .product-add-form form #product-addtocart-button').trigger('click');
            });


        });
    })(jQuery);
});
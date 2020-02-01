require([
    'jquery'
], function (jQuery) {
    (function ($) {
        $(document).ready(function () {
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
                $('.ingredient').toggleClass('active');
            });
            $(".description .title").click(function () {
                $('.description .content').slideToggle();
                $(this).toggleClass('show hide');
            });

            /* STICKY ADD TO CART BUTTON ON MOBILE PRODUCT PAGE */
            if ($('.product-info-main .product-add-form form #product-addtocart-button').length) {
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
                var text = $('.product-mobile-title .sticky-buttons .sticky-atc-button').data('text');
                var price = $('.product-mobile-title .price-box.price-final_price [data-price-type="finalPrice"] .price').text();
                if (price) {
                    $('.product-mobile-title .sticky-buttons .sticky-atc-button').text(text + ' - ' + price);
                }
                $('.sticky-buttons .sticky-atc-button').click(function (e) {
                    e.preventDefault();
                    $('.product-info-main .product-add-form form #product-addtocart-button').trigger('click');
                });
            }
        });
    })(jQuery);
});

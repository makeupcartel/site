require([
    'jquery'
], function ($) {
    $(document).ready(function () {
        $('.product-list.increase-qty').click(function () {
            var productId = $(this).attr('product-id');
            if ($('#qty' + productId).val() >= 2) {
                $('#qty' + productId).val(parseInt($('#qty' + productId).val()) - 1);
            }
        });
        $('.product-list.decrease-qty').click(function () {
            var productId = $(this).attr('product-id');
            $('#qty' + productId).val(parseInt($('#qty' + productId).val()) + 1);
        });
        $('.quick-order.action.tocart.primary').click(function (evt) {
            var productId = $(this).attr('product-id');
            var selectOption = $('.swatch-opt-' + productId).find('.swatch-attribute.shade .swatch-select');
            if (selectOption.val() == 0) {
                $('.swatch-opt-' + productId).find('.swatch-attribute.shade').addClass('error');
                $('.swatch-opt-' + productId).find('.swatch-attribute.shade .mage-error').show();
                evt.preventDefault();
            } else {
                $('.swatch-opt-' + productId).find('.swatch-attribute.shade').removeClass('error');
                $('.swatch-opt-' + productId).find('.swatch-attribute.shade .mage-error').hide();
            }
        });
    });
});
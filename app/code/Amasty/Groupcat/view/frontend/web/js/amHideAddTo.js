define([
    "jquery",
    "jquery/ui"
], function ($) {
    $.widget('mage.amHideAddTo', {
        options: {},
        _create: function () {
            if (this.element) {
                var parent = this.element.parents(this.options['parent']);
                if (!parent) {
                    return;
                }
                if (this.options['hide_compare'] === '1') {
                    parent.find('a.tocompare').remove();
                }
                if (this.options['hide_wishlist'] === '1') {
                    parent.find('a.towishlist').remove();
                }
            }
        }
    });

    return $.mage.amHideAddTo;
});

define([
    'jquery',
    'jquery/ui',
    'uiRegistry',
    'Magento_Ui/js/modal/alert'
], function ($, jQueryUi, registry, alert) {

    $.widget('mage.amreportsToolbar', {
        options: {
            contentSelector: '[data-role="amreports-content"]',
            componentName: 'amreports_reload=1'
        },

        form: null,

        _create: function () {
            if (this.element.hasClass('control-value')) {
                this.form = $(this.element).closest('form');
                $(this.element).click($.proxy(this.reload, this));
            } else {
                this.form = $(this.element).find('form');
                $(this.form).change($.proxy(this.reload, this));
            }
        },
        
        reload: function () {
            var formData = $(this.form).serializeArray(),
                requestData = {};

            for (var i = 0; i < formData.length; i++) {
                var input = formData[i];

                requestData[input.name] = input.value;
            }

            var contentBlock = $(this.options.contentSelector),
                errorMessage = '[data-amreports-js="amreports-error"]';

            contentBlock.css({opacity: .3});

            if (requestData.sku !== undefined && requestData.sku === '') {
                if (!$(errorMessage).length) {
                    $('.amreport-sku-field').before(
                        '<label for="sku" class="amreport-error" data-amreports-js="amreports-error">' + $.mage.__('This is a required field.') +'</label>'
                    );
                    setTimeout(
                        function () {
                            $(errorMessage).fadeOut("slow", function () {
                                $(errorMessage).remove();
                            });
                        },
                        3000
                    );
                }

                return;
            }
            $.ajax({
                url: '',
                method: 'GET',
                data: {amreports: requestData},
                success: function (response) {
                    if (response.error) {
                        alert({
                            content: response.error
                        });
                        return;
                    }
                    if ('AmCharts' in window) {
                        AmCharts.clear();
                    }
                    contentBlock.html($(response).html());
                    contentBlock.css({opacity: 1});
                    contentBlock.trigger('contentUpdated');
                }
            });

            var dataSource = registry.get(this.options.componentName);

            if (dataSource) {
                dataSource.params.amreports = requestData;
                dataSource.reload();
            }
        }
    });

    return $.mage.amreportsToolbar;
});

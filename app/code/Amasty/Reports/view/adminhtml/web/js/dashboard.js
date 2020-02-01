define([
    'jquery',
    'jquery/ui',
    'uiRegistry',
    'Magento_Ui/js/modal/alert',
    'Amasty_Reports/js/charts/semiCirclePieChart',
    'Amasty_Reports/js/charts/linearCharts'
], function ($, jQueryUi, registry, alert, semiCirclePieChart, linearCharts) {

    $.widget('mage.amreportsDashboard', {
        options: {
            contentSelector: '[data-role="amreports-content"]',
            componentName: 'amreports_reload=1',
            widgetSelector: '.-widgets'
        },

        funnelForm: null,
        salesForm: null,
        storeForm: null,

        _create: function () {
            var self = this,
                optionsContainer ='[data-reports-js="options-container"]';

            this.funnelForm = $(this.element).find('#funnel');
            $(this.funnelForm).change($.proxy(this.funnelFormReload, this));

            this.salesForm = $(this.element).find('#salesForm');
            $(this.salesForm).change($.proxy(this.salesFormReload, this));

            this.storeForm = $('[data-role="amreports-toolbar"]').find('#report_toolbar');
            $(this.storeForm).change($.proxy(this.changeStore, this));

            $('[data-reports-js="options-item"]').on( "click", function() {
                self.changeWidget($(this));
                $(optionsContainer).hide();
            });

            $("body").on( "click", function() {
                $(optionsContainer).hide();
            });

            $('[data-amreports-js="tab-funnel-rate"]').on( "click", function(e) {
                self.openReport(e);
            });

            $('[data-reports-js="options-switch"]').on( "click", function(e) {
                if (!$(document.activeElement).is('a')) {
                    $(optionsContainer).hide();
                    $(this).find(optionsContainer).toggle();
                    e.stopPropagation();
                }
            });

            $(document).ready(function() {
                self.funnelFormReload();
                self.salesFormReload();
            });
        },

        changeStore: function() {
            var formData = $(this.storeForm).serializeArray(),
                requestData = {},
                reloadUrl = $(this.storeForm).attr('action');

            for (var i = 0; i < formData.length; i++) {
                var input = formData[i],
                    regexp = '(' + input.name + ')\\/\\d+';

                reloadUrl = reloadUrl.replace(new RegExp(regexp), '$1/' + input.value);
            }

            window.location.href = reloadUrl;
        },

        changeWidget: function(elem) {
            var requestData = {},
                widget = '[data-reports-js="widget"]',
                self = this;

            requestData['parent'] = elem.data('parent');
            requestData['widget'] = elem.attr('name');
            requestData['group'] = $('[data-reports-tabs].-current').attr('data-amrepgroup-js');
            $.ajax({
                url: '',
                method: 'GET',
                data: {amreports: requestData, 'amaction': 'widget'},
                beforeSend: function () {
                    self._showLoader();
                },
                success: function (response) {
                    var detailLink = elem.parents(widget).find('[data-reports-js="detail-widget"]');
                    elem.parents(widget).find('[data-reports-js="header-widget"]').html(response.title);
                    elem.parents(widget).find('[data-reports-js="total-widget"]').html(response.value);
                    if (typeof response.link !== 'undefined') {
                        detailLink.removeClass('amreports-detail-disabled');
                        detailLink.attr('href', response.link);
                    } else if (!detailLink.hasClass('amreports-detail-disabled')) {
                        detailLink.addClass('amreports-detail-disabled');
                    }
                }
            }).always(function () {
                self._hideLoader();
            });
        },

        funnelFormReload: function () {
            var formData = $(this.funnelForm).serializeArray(),
                requestData = {},
                self = this;

            for (var i = 0; i < formData.length; i++) {
                var input = formData[i];
                requestData[input.name] = input.value;
            }

            var contentBlock = $(this.options.contentSelector);
            contentBlock.css({opacity: .3});
            $.ajax({
                url: '',
                method: 'GET',
                data: {amreports: requestData, 'amaction': 'funnel'},
                beforeSend: function () {
                    self._showLoader();
                },
                success: function (response) {
                    if (response.error) {
                        self.createAlertError(response.error);
                        return;
                    }
                    var data = JSON.parse(response);
                    $('#viewedCount').html(Math.ceil(data.viewedCount));
                    $('#addedCount').html(Math.ceil(data.addedCount));
                    $('#orderedCount').html(Math.ceil(data.orderedCount));
                    $('#uniqueCount').html(Math.ceil(data.uniqueVisitors));

                    $('#notViewed').html(data.notViewed);
                    $('#abandoned').html(data.abandoned);

                    semiCirclePieChart({'dataFunnel': data});
                }
            }).always(function () {
                self._hideLoader();
            });
        },

        salesFormReload: function () {
            var formData = $(this.salesForm).serializeArray(),
                requestData = {},
                self = this;

            for (var i = 0; i < formData.length; i++) {
                var input = formData[i];
                requestData[input.name] = input.value;
            }

            var contentBlock = $(this.options.contentSelector);
            contentBlock.css({opacity: .3});
            $.ajax({
                url: '',
                method: 'GET',
                data: {amreports: requestData, 'amaction': 'sales'},
                beforeSend: function () {
                    self._showLoader();
                },
                success: function (response) {
                    if (response.error) {
                        self.createAlertError(response.error);
                        return;
                    }
                    chartData = [];
                    for(var i=0; i<response.items.length;i++) {
                        chartData.push({
                            date: response.items[i].period,
                            value: response.items[i].total
                        });
                    }

                    if (chartData.length) {
                        $(self.salesForm).find('#chart-overview').removeClass('amreports-not-found-data');
                        $(self.salesForm).find('.not-found-data-message').hide();
                        linearCharts({'data': chartData,'interval': 'day'});
                    } else {
                        $(self.salesForm).find('#chart-overview').addClass('amreports-not-found-data');
                        $(self.salesForm).find('.not-found-data-message').show();
                    }

                }
            }).always(function () {
                self._hideLoader();
            });
        },

        widgetFormReload: function () {
            var formData = $(this.salesForm).serializeArray(),
                requestData = {},
                self = this;

            for (var i = 0; i < formData.length; i++) {
                var input = formData[i];
                requestData[input.name] = input.value;
            }

            var contentBlock = $(this.options.contentSelector);
            contentBlock.css({opacity: .3});
            $.ajax({
                url: '',
                method: 'GET',
                data: {amreports: requestData, 'amaction': 'sales'},
                beforeSend: function () {
                    self._showLoader();
                },
                success: function (response) {

                }
            }).always(function () {
                self._hideLoader();
            });
        },

        createAlertError: function (error) {
            if (error) {
                alert({
                    content: error
                });
            }
        },

        openReport: function (event) {
            var i, tabContent, tabLinks, reportName;

            tabContent = $('[data-amreports-js="tabcontent"]');
            tabContent.addClass('-hidden');

            $('[data-amreports-js="tab-funnel-rate"]').removeClass('active');
            reportName = event.currentTarget.classList.contains('funnel') ? "funnel" : "rate";
            $("#" + reportName + "_tab").removeClass('-hidden');

            $(event.currentTarget).addClass("active");
        },

        _showLoader: function () {
            $('body').trigger('processStart', [$('.amreports-dashboard-container')]);
        },

        _hideLoader: function () {
            $('body').trigger('processStop');
        }
    });

    return $.mage.amreportsDashboard;
});

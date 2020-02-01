define([
    'jquery',
    'mage/translate',
    'Amasty_Reports/vendor/amcharts4/core.min',
    'Amasty_Reports/vendor/amcharts4/charts.min',
    'Amasty_Reports/vendor/amcharts4/animated.min'
], function ($) {
    'use strict';

    $.widget('mage.Charts', {
        options: {
            dataFunnel: {},
            funnelFirstInitSelector: 'chart-funnel-first',
            rateFirstInitSelector: 'chart-rate-first',
            funnelSecondInitSelector: 'chart-funnel-second',
            firstLegendInitSelector: 'legend-div',
            secondLegendInitSelector: 'legend-div-second',
            rateLegendInitSelector: 'legend-div-rate'
        },

        _create: function () {
            this.renderConversionChart();
            this.renderConversionCharBottom();
        },

        renderConversionChart: function () {
            var self = this,
                funnelChart = am4core.create(self.options.funnelFirstInitSelector,am4charts.PieChart),
                rateChart = am4core.create(self.options.rateFirstInitSelector,am4charts.PieChart);

            am4core.useTheme(am4themes_animated);

            self.prepareFunnelChart(funnelChart, self.options.dataFunnel);
            self.prepareRateChart(rateChart, self.options.dataFunnel);
            self.renderChart(funnelChart, self.options.firstLegendInitSelector);
            self.renderChart(rateChart, self.options.rateLegendInitSelector);
        },

        renderChart: function (chart, legendSelector) {
            var self = this;

            self.renderSeries(chart, "#78b5d9", "#7094D6");
            self.renderLegends(chart, legendSelector);

            // Enable export
            chart.exporting.menu = new am4core.ExportMenu();
            chart.exporting.menu.items = [{
                label: "...",
                menu: [{
                    label: "Image",
                    menu: [
                        {type: "png", label: "PNG"},
                        {type: "jpg", label: "JPG"},
                        {type: "svg", label: "SVG"},
                        {type: "pdf", label: "PDF"}
                    ]
                }, {
                    label: "Print", type: "print"
                }
                ]
            }];
        },

        prepareFunnelChart: function (chart, data) {
            var notInterestedPercent = 0;
            if (data.viewedPercent) {
                notInterestedPercent = Math.round(100 - data.viewedPercent)
            } else if (data.notViewed) {
                notInterestedPercent = 100;
            }
            chart.data = [{
                interest: $.mage.__("Interested"),
                value: data.viewedPercent,
                number: ' (' + data.viewedCount + ')'
            },{
                interest: $.mage.__("Not interested"),
                value: notInterestedPercent,
                number: ' (' + data.notViewed + ')'
            }];
        },

        prepareRateChart: function (chart, data) {
            var lostOrdersPercent = 0;
            if (data.ordersPercent) {
                lostOrdersPercent = Math.round(100 - data.ordersPercent)
            } else if (data.lostOrdersCount) {
                lostOrdersPercent = 100;
            }
            chart.data = [{
                interest: $.mage.__("Orders placed"),
                value: data.ordersPercent,
                number: ' (' + (data.placedOrdersCount || 0) + ')'
            },{
                interest: $.mage.__("Lost orders"),
                value: lostOrdersPercent,
                number: ' (' + (data.lostOrdersCount || 0) + ')'
            }];
        },

        renderConversionCharBottom: function () {
            var self = this,
                chart = am4core.create(self.options.funnelSecondInitSelector, am4charts.PieChart),
                orderedPercent = 0;

            am4core.useTheme(am4themes_animated);

            if (self.options.dataFunnel.addedPercent) {
                orderedPercent = Math.round(100 - self.options.dataFunnel.addedPercent)
            } else if (self.options.dataFunnel.orderedCount) {
                orderedPercent = 100;
            }

            chart.data = [{
                interest: $.mage.__("Abandoned carts"),
                value: Math.round(self.options.dataFunnel.addedPercent),
                number: ' (' + self.options.dataFunnel.abandoned + ')'
            },{
                interest: $.mage.__("Ordered"),
                value: orderedPercent,
                number: ' (' + self.options.dataFunnel.orderedCount + ')'
            }];

            self.renderSeries(chart, "#6872d5", "#7c69d6");
            self.renderLegends(chart, self.options.secondLegendInitSelector);

            // Enable export
            chart.exporting.menu = new am4core.ExportMenu();
            chart.exporting.menu.items = [{
                label: "...",
                menu: [{
                    label: $.mage.__("Image"),
                    menu: [
                        { type: "png", label: "PNG" },
                        { type: "jpg", label: "JPG" },
                        { type: "svg", label: "SVG" },
                        { type: "pdf", label: "PDF" }
                    ]
                }, {
                    label: $.mage.__("Print"), type: "print"
                }
                ]
            }];
        },

        renderSeries: function (chart, colorOne, colorTwo) {
            chart.padding(5, 10, 20, 10);
            chart.radius = am4core.percent(100);
            chart.innerRadius = am4core.percent(35);
            chart.startAngle = 180;
            chart.endAngle = 360;
            chart.hiddenState.properties.opacity = 0;

            var series = chart.series.push(new am4charts.PieSeries());
            series.dataFields.value = "value";
            series.dataFields.category = "interest";
            series.legendSettings.labelText = "[#363636]{interest}{number}[/]";
            series.legendSettings.valueText = "[font-size: 20px #363636]{value}%[/]";
            series.slices.template.cornerRadius = 3;
            series.slices.template.innerCornerRadius = 3;
            series.slices.template.stroke = am4core.color("#fff");
            series.slices.template.strokeWidth = 3;
            series.slices.template.draggable = false;
            series.slices.template.inert = true;
            series.slices.template.clickable = false;
            series.slices.template.tooltipText = "[bold]{category}:[/] [font-size:14px]{value}%";
            series.ticks.template.disabled = true;
            series.labels.template.disabled = true;
            series.alignLabels = false;
            series.hiddenState.properties.startAngle = 90;
            series.hiddenState.properties.endAngle = 90;
            series.colors.list = [
                am4core.color(colorOne),
                am4core.color(colorTwo)
            ];
        },

        renderLegends: function (chart, id) {
            chart.legend = new am4charts.Legend();

            var legendContainer = am4core.create(id, am4core.Container),
                markerTemplate,
                marker;

            legendContainer.width = am4core.percent(50);
            legendContainer.height = am4core.percent(100);
            legendContainer.paddingTop = 24;

            chart.legend.parent = legendContainer;
            chart.legend.labels.template.fill = "#363636";
            chart.legend.useDefaultMarker = true;
            chart.legend.labels.template.truncate = false;
            chart.legend.itemContainers.template.paddingTop = 0
            chart.legend.labels.template.wrap = true;

            if (window.innerWidth >= 1024) {
                chart.legend.labels.template.wrap = false;
            }

            markerTemplate = chart.legend.markers.template;
            markerTemplate.width = 10;
            markerTemplate.height = 10;
            marker = chart.legend.markers.template.children.getIndex(0);
            marker.cornerRadius(15, 15, 15, 15);
        }
    });

    return $.mage.Charts;
});

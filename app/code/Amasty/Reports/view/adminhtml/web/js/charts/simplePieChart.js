define([
    'jquery',
    'Amasty_Reports/vendor/amcharts4/core.min',
    'Amasty_Reports/vendor/amcharts4/charts.min',
    'Amasty_Reports/vendor/amcharts4/animated.min'
], function ($) {
    'use strict';

    $.widget('mage.categoryCharts', {
        options: {
            data: window.chartData,
            selectorInit: 'chart-category'
        },

        _create: function () {
            this.renderCategoryChart();
        },

        renderCategoryChart: function () {
            var self = this,
                chart = am4core.create(self.options.selectorInit, am4charts.PieChart);

            am4core.useTheme(am4themes_animated);

            chart.data = self.options.data;

            // Add and configure Series
            var pieSeries = chart.series.push(new am4charts.PieSeries());
            pieSeries.dataFields.value = "value";
            pieSeries.dataFields.category = "title";
            pieSeries.slices.template.stroke = am4core.color("#fff");
            pieSeries.slices.template.strokeWidth = 2;
            pieSeries.slices.template.strokeOpacity = 1;

            // This creates initial animation
            pieSeries.hiddenState.properties.opacity = 1;
            pieSeries.hiddenState.properties.endAngle = -90;
            pieSeries.hiddenState.properties.startAngle = -90;

            chart.responsive.enabled = true;
            chart.responsive.rules.push({
                relevant: function(target) {
                    if (target.pixelWidth <= 770) {
                        return true;
                    }

                    return false;
                },
                state: function(target, stateId) {
                    pieSeries.ticks.template.disabled = true;
                    pieSeries.alignLabels = false;
                    pieSeries.labels.template.text = "{value.percent.formatNumber('#.0')}%";
                    pieSeries.labels.template.radius = am4core.percent(-40);
                    return;
                }
            });

            // Enable export
            chart.exporting.menu = new am4core.ExportMenu();
            chart.exporting.menu.items = [{
                label: "...",
                menu: [{
                    label: "Image",
                    menu: [
                        { type: "png", label: "PNG" },
                        { type: "jpg", label: "JPG" },
                        { type: "svg", label: "SVG" },
                        { type: "pdf", label: "PDF" }
                    ]
                }, {
                    label: "Print", type: "print"
                }
                ]
            }];
        }
    });

    return $.mage.categoryCharts;
});

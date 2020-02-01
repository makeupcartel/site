define([
    'jquery',
    'mage/translate',
    'Amasty_Reports/vendor/amcharts4/core.min',
    'Amasty_Reports/vendor/amcharts4/charts.min',
    'Amasty_Reports/vendor/amcharts4/animated.min'
], function ($) {
    'use strict';

    $.widget('mage.multiColumnCharts', {
        options: {
            data: {},
            selectorInit: 'chart-customers'
        },

        _create: function () {
            this.renderMultiColumnChart();
        },

        renderMultiColumnChart: function () {
            var self = this,
                chart = am4core.create(self.options.selectorInit, am4charts.XYChart),
                categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis()),
                valueAxis = chart.yAxes.push(new am4charts.ValueAxis()),
                data = self.options.data,
                chartIdentifier = self.element.data('chart-identifier');

            am4core.useTheme(am4themes_animated);

            chart.data = data;

            categoryAxis.dataFields.category = "date";
            categoryAxis.renderer.grid.template.location = 0;
            categoryAxis.renderer.minGridDistance = 30;

            if (chartIdentifier === 'customers') {
                self.createSeries(chart, "accounts", "date", $.mage.__("Accounts"), 100);
                self.createSeries(chart, "orders", "date", $.mage.__("Orders"), 75);
                self.createSeries(chart, "reviews", "date", $.mage.__("Reviews"), 50);

            } else if (chartIdentifier === 'returning-customers') {
                self.createSeries(chart, "new_customers", "date", $.mage.__("New customers"), 100);
                self.createSeries(chart, "returning_customers", "date", $.mage.__("Returning customers"), 50);
                categoryAxis.renderer.inversed = true;
            } else if (chartIdentifier === 'conversion') {
                self.createSeries(chart, "conversion", "date", $.mage.__("Conversion Rate"), 100);
                categoryAxis.renderer.inversed = true;
            }

            // Add legend
            chart.legend = new am4charts.Legend();
            chart.legend.contentAlign = "left";
            chart.legend.itemContainers.template.paddingLeft = 15;

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
        },

        createSeries: function (chart, value, category, label) {
            var series = chart.series.push(new am4charts.ColumnSeries());

            series.dataFields.valueY = value;
            series.dataFields.categoryX = category;
            series.columns.template.tooltipText = "" + $.mage.__(label) + ": [bold]{valueY}[/]";
            series.legendSettings.labelText = ""+ $.mage.__(label) +" ";
        }
    });

    return $.mage.multiColumnCharts;
});

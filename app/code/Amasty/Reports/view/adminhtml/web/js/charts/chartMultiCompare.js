define([
    'jquery',
    'Amasty_Reports/vendor/amcharts4/charts.min',
    'Amasty_Reports/vendor/amcharts4/animated.min'
], function ($) {
    'use strict';

    $.widget('mage.compareCharts', {
        options: {
            data: {},
            selectorInit: 'chart-compare'
        },

        _create: function () {
            this.renderCompareChart();
        },

        renderCompareChart: function () {
            var self = this,
                chart = am4core.create(self.options.selectorInit, am4charts.XYChart),
                data = self.options.data;

            am4core.useTheme(am4themes_animated);

            chart.data = data;

            var dateAxis = chart.xAxes.push(new am4charts.DateAxis());
            dateAxis.renderer.minGridDistance = 60;
            if (Object.keys(data[0]).length > 2) {
                dateAxis.renderer.disabled = true
            }

            var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());

            function createSeries(value, color, val) {
                var series = chart.series.push(new am4charts.LineSeries());
                series.dataFields.valueY = value;
                series.dataFields.dateX = "date";
                series.tooltipText = val;
                series.stroke = am4core.color(color);
                series.tooltip.pointerOrientation = "vertical";
                series.fill = am4core.color(color);
                series.fillOpacity = 0.2;
            }
            if (self.options.interval === 'year') {
                dateAxis.baseInterval = {
                    "timeUnit": "year",
                    "count": 1
                };
            }

            createSeries('orders_0', '#78b5d9', "{orders_0}");
            createSeries('orders_1', '#6f94d7', "{orders_1}");
            createSeries('orders_2', '#7c69d6', "{orders_2}");

            chart.cursor = new am4charts.XYCursor();
            chart.cursor.xAxis = dateAxis;
            chart.scrollbarX = new am4core.Scrollbar();
            chart.zoomOutButton.marginRight = 30;

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

    return $.mage.compareCharts;
});

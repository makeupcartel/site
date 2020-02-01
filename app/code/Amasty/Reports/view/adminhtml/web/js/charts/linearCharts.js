define([
    'jquery',
    'Amasty_Reports/vendor/amcharts4/core.min',
    'Amasty_Reports/vendor/amcharts4/charts.min',
    'Amasty_Reports/vendor/amcharts4/animated.min'
], function ($) {
    'use strict';

    $.widget('mage.linearCharts', {
        options: {
            data: {},
            selectorInit: 'chart-overview'
        },

        _create: function () {
            this.renderSalesChart();
        },

        renderSalesChart: function () {
            var self = this,
                chart = am4core.create(self.options.selectorInit, am4charts.XYChart),
                chartIdentifier = self.element.data('chart-identifier');

            am4core.useTheme(am4themes_animated);

            chart.data = self.options.data;

            var dateAxis = chart.xAxes.push(new am4charts.DateAxis());

            dateAxis.renderer.grid.template.location = 0;
            dateAxis.renderer.minGridDistance = 50;
            dateAxis.renderer.labels.template.location = 0.5;

            if (self.options.interval === 'year') {
                dateAxis.baseInterval = {
                    "timeUnit": "year",
                    "count": 1
                };
            } else if (self.options.interval === 'day' || self.options.interval === '0') {
                dateAxis.baseInterval = {
                    "timeUnit": "day",
                    "count": 1
                };
            }

            var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());

            var series = chart.series.push(new am4charts.LineSeries());
            series.dataFields.dateX = "date";
            if (chartIdentifier === 'abandoned') {
                series.dataFields.valueY = "count";
            } else {
                series.dataFields.valueY = "value";
            }
            series.tooltipText = "{value}";
            series.tooltip.pointerOrientation = "vertical";

            chart.cursor = new am4charts.XYCursor();
            chart.cursor.snapToSeries = series;
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

    return $.mage.linearCharts;
});

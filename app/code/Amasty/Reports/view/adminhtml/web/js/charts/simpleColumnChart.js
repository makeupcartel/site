define([
    'jquery',
    'mage/translate',
    'Amasty_Reports/vendor/amcharts4/core.min',
    'Amasty_Reports/vendor/amcharts4/charts.min',
    'Amasty_Reports/vendor/amcharts4/animated.min'
], function ($, $t) {
    'use strict';

    $.widget('mage.columnCharts', {
        options: {
            data: {},
            selectorInit: 'chart-column'
        },

        _create: function () {
            this.renderColumnChart();
        },

        renderColumnChart: function () {
            var self = this,
                chart = am4core.create(self.options.selectorInit, am4charts.XYChart),
                chartIdentifier = self.element.data('chart-identifier');

            am4core.useTheme(am4themes_animated);

            chart.data = self.options.data;

            var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
            if (chartIdentifier == 'attributes') {
                categoryAxis.dataFields.category = "attribute";
            } else if (chartIdentifier == 'title') {
                categoryAxis.dataFields.category = "title";
            } else {
                categoryAxis.dataFields.category = "date";
            }

            categoryAxis.renderer.grid.template.location = 0;
            categoryAxis.renderer.minGridDistance = 30;

            var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
            valueAxis.min = 0;
            if (chartIdentifier === 'abandoned') {
                valueAxis.title.text = $t('Number of Abandoned Carts');
                valueAxis.maxPrecision = 0;
            }

            var label = categoryAxis.renderer.labels.template;
            label.truncate = true;

            categoryAxis.events.on("sizechanged", function(ev) {
                var axis = ev.target;
                var cellWidth = axis.pixelWidth / (axis.endIndex - axis.startIndex);
                axis.renderer.labels.template.maxWidth = cellWidth;
            });

            function createSeriesOptions(identifier) {
                var optionsChart = {
                    'payments': {
                        'value': 'payments',
                        'category': 'date'
                    },
                    'attributes': {
                        'value': 'value',
                        'category': 'attribute'
                    },
                    'coupon': {
                        'value': 'coupon',
                        'category': 'date'
                    },
                    'default': {
                        'value': 'visits',
                        'category': 'date'
                    },
                    'abandoned': {
                        'value': 'count',
                        'category': 'date'
                    },
                    'value': {
                        'value': 'value',
                        'category': 'date'
                    },
                    'title': {
                        'value': 'value',
                        'category': 'title'
                    }
                };

                series.dataFields.valueY = optionsChart[identifier]['value'];
                series.dataFields.categoryX = optionsChart[identifier]['category'];
                if (identifier === 'abandoned') {
                    series.columns.template.tooltipText = "[bold] {valueY} " + $t('Abandoned Carts') + "[/]";
                } else {
                    series.columns.template.tooltipText = "[bold]" + self.options.currency + "{valueY}[/]";
                }
                series.columns.template.fillOpacity = .8;
            }

            // Create series
            var series = chart.series.push(new am4charts.ColumnSeries());
            createSeriesOptions(chartIdentifier);

            var columnTemplate = series.columns.template;
            columnTemplate.strokeWidth = 2;
            columnTemplate.strokeOpacity = 1;

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

    return $.mage.columnCharts;
});

define([
    'jquery',
    'mage/translate',
    'Amasty_Reports/vendor/amcharts4/core.min',
    'Amasty_Reports/vendor/amcharts4/charts.min',
    'Amasty_Reports/vendor/amcharts4/animated.min'
], function ($, $t) {
    'use strict';

    $.widget('mage.horizontalColumnCharts', {
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
                categoryAxis = chart.yAxes.push(new am4charts.CategoryAxis()),
                valueAxis = chart.xAxes.push(new am4charts.ValueAxis()),
                label = categoryAxis.renderer.labels.template,
                chartIdentifier = self.element.data('chart-identifier');

            am4core.useTheme(am4themes_animated);

            chart.data = self.options.data;

            if (chartIdentifier == 'title') {
                categoryAxis.dataFields.category = "title";
            }

            categoryAxis.renderer.grid.template.location = 0;
            categoryAxis.renderer.minGridDistance = 10;

            label.truncate = true;
            label.fontSize = 12;
            label.renderingFrequency = 1;

            // Create series
            self.createSeriesOptions(chartIdentifier, chart, categoryAxis);

            // Enable export
            chart.exporting.menu = new am4core.ExportMenu();
            chart.exporting.menu.items = [{
                label:  "...",
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

        createSeriesOptions: function (identifier, chart, categoryAxis) {
            var self = this,
                series = chart.series.push(new am4charts.ColumnSeries()),
                columnTemplate = series.columns.template,
                optionsChart = {
                    'title': {
                        'value': 'value',
                        'category': 'title'
                    }
                };

            series.dataFields.valueX = optionsChart[identifier]['value'];
            series.dataFields.categoryY = optionsChart[identifier]['category'];

            series.columns.template.tooltipText = "{title}:[bold] " + self.options.currency + "{valueX}[/]";
            series.columns.template.fillOpacity = .8;

            columnTemplate.strokeWidth = 2;
            columnTemplate.strokeOpacity = 1;

            self.createResponsiveBehavior(chart, categoryAxis);
        },

        createResponsiveBehavior: function (chart, categoryAxis) {
            chart.responsive.enabled = true;
            chart.responsive.rules.push({
                relevant: function(target) {
                    if (target.pixelWidth <= 300) {
                        return true;
                    }

                    return false;
                },
                state: function() {
                    categoryAxis.renderer.labels.template.disabled = true;
                }
            });
        }
    });

    return $.mage.horizontalColumnCharts;
});

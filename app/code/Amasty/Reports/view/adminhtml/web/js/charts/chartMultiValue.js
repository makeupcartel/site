define([
    'jquery',
    'mage/translate',
    'Amasty_Reports/vendor/amcharts4/core.min',
    'Amasty_Reports/vendor/amcharts4/charts.min',
    'Amasty_Reports/vendor/amcharts4/animated.min'
], function ($) {
    'use strict';

    $.widget('mage.returningCharts', {
        options: {
            data: window.chartData,
            params: {},
            selectorInit: 'chart-customers'
        },

        _create: function () {
            this.renderCustomersChart();
        },

        renderCustomersChart: function () {
            var self = this,
                chart = am4core.create(self.options.selectorInit, am4charts.XYChart),
                data = self.options.data,
                chartIdentifier = self.element.data('chart-identifier');

            am4core.useTheme(am4themes_animated);

            chart.data = data;

            var dateAxis = chart.xAxes.push(new am4charts.DateAxis());
            dateAxis.renderer.minGridDistance = 60;

            function createAxisAndSeries(field, name, bullet) {
                var series = chart.series.push(new am4charts.LineSeries());
                series.dataFields.valueY = field;
                series.dataFields.dateX = "date";
                series.strokeWidth = 2;
                series.yAxis = valueAxis;
                series.name = name;
                series.tooltipText = "{name}: [bold]{valueY}[/]";

                var interfaceColors = new am4core.InterfaceColorSet();

                switch(bullet) {
                    case "triangle":
                        var bullet = series.bullets.push(new am4charts.Bullet());
                        bullet.width = 12;
                        bullet.height = 12;
                        bullet.horizontalCenter = "middle";
                        bullet.verticalCenter = "middle";

                        var triangle = bullet.createChild(am4core.Triangle);
                        triangle.stroke = interfaceColors.getFor("background");
                        triangle.strokeWidth = 2;
                        triangle.direction = "top";
                        triangle.width = 12;
                        triangle.height = 12;
                        break;
                    case "rectangle":
                        var bullet = series.bullets.push(new am4charts.Bullet());
                        bullet.width = 10;
                        bullet.height = 10;
                        bullet.horizontalCenter = "middle";
                        bullet.verticalCenter = "middle";

                        var rectangle = bullet.createChild(am4core.Rectangle);
                        rectangle.stroke = interfaceColors.getFor("background");
                        rectangle.strokeWidth = 2;
                        rectangle.width = 10;
                        rectangle.height = 10;
                        break;
                    default:
                        var bullet = series.bullets.push(new am4charts.CircleBullet());
                        bullet.circle.stroke = interfaceColors.getFor("background");
                        bullet.circle.strokeWidth = 2;
                        break;
                }
            }

            if (!self.options.params.interval || self.options.params.interval === '0') {
                self.options.params.interval = 'day';
            }

            if (self.options.params.interval != 'week') {
                dateAxis.baseInterval = {
                    "timeUnit": self.options.params.interval,
                    "count": 1
                };
            }

            var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
            valueAxis.renderer.line.strokeOpacity = 1;

            if (chartIdentifier === 'customers') {
                createAxisAndSeries("accounts", $.mage.__("Accounts"),"circle");
                createAxisAndSeries("orders", $.mage.__("Orders"), "rectangle");
                createAxisAndSeries("reviews", $.mage.__("Reviews"), "triangle");
            } else if (chartIdentifier === 'returning-customers') {
                createAxisAndSeries("new_customers", $.mage.__("New customers"),"circle");
                createAxisAndSeries("returning_customers", $.mage.__("Returning customers"), "rectangle");
            } else if (chartIdentifier === 'conversion') {
                createAxisAndSeries("conversion", $.mage.__("Conversion Rate"), "circle");
            }

            // Add legend
            chart.legend = new am4charts.Legend();
            chart.legend.contentAlign = "left";
            chart.legend.itemContainers.template.paddingLeft = 15;

            // Add cursor
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

    return $.mage.returningCharts;
});

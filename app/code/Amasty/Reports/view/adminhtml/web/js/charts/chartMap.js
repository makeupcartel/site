define([
    'jquery',
    'mage/translate',
    'Amasty_Reports/vendor/amcharts4/core.min',
    'Amasty_Reports/vendor/amcharts4/maps.min',
    'Amasty_Reports/vendor/amcharts4/geodata/worldHigh.min',
    'Amasty_Reports/vendor/amcharts4/animated.min'
], function ($) {
    'use strict';

    $.widget('mage.countryCharts', {
        options: {
            data: window.chartData,
            selectorInit: 'chart-country'
        },

        _create: function () {
            this.renderCountryChart();
        },

        renderCountryChart: function () {
            var self = this,
                chart = am4core.create(self.options.selectorInit, am4maps.MapChart);

            am4core.useTheme(am4themes_animated);

            // Set map definition
            chart.geodata = am4geodata_worldHigh;

            // Set projection
            chart.projection = new am4maps.projections.Mercator();

            // Export
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

            // Zoom control
            chart.zoomControl = new am4maps.ZoomControl();

            var homeButton = new am4core.Button();
            homeButton.events.on("hit", function(){
                chart.goHome();
            });

            homeButton.icon = new am4core.Sprite();
            homeButton.padding(7, 5, 7, 5);
            homeButton.width = 30;
            homeButton.icon.path = "M16,8 L14,8 L14,16 L10,16 L10,10 L6,10 L6,16 L2,16 L2,8 L0,8 L8,0 L16,8 Z M16,8";
            homeButton.marginBottom = 10;
            homeButton.parent = chart.zoomControl;
            homeButton.insertBefore(chart.zoomControl.plusButton);

            // Center on the groups by default
            chart.homeZoomLevel = 3.5;
            chart.homeGeoPoint = { longitude: 10, latitude: 52 };

            var excludedCountries = ["AQ"];
            var includedCountries = [];
            self.options.data.forEach(function(country){
                includedCountries.push(country.id);
                excludedCountries.push(country.id);
            });

            var polygonSeries = chart.series.push(new am4maps.MapPolygonSeries()),
                polygonTemplate = polygonSeries.mapPolygons.template,
                label = $.mage.__('Number Of Orders -'),
                currency = self.options.currency;
            if (self.options.type === 'total') {
                label = $.mage.__('Total -');
            }
            polygonTemplate.tooltipText = "[bold]{name}:[/]  " + label + " " + currency + "{value}";
            polygonSeries.heatRules.push({
                property: "fill",
                target: polygonSeries.mapPolygons.template,
                min: chart.colors.getIndex(1).brighten(1),
                max: chart.colors.getIndex(1).brighten(-0.3)
            });
            polygonSeries.useGeodata = true;

            // add heat legend
            var heatLegend = chart.chartContainer.createChild(am4maps.HeatLegend);
            heatLegend.series = polygonSeries;
            heatLegend.align = "left";
            heatLegend.width = am4core.percent(25);
            heatLegend.marginLeft = am4core.percent(4);
            heatLegend.valign = "bottom";
            heatLegend.marginBottom = 15;
            heatLegend.valueAxis.renderer.labels.template.fontSize = 10;
            heatLegend.valueAxis.renderer.minGridDistance = 40;

            // The rest of the world.
            var worldSeries = chart.series.push(new am4maps.MapPolygonSeries());
            var worldSeriesName = "world";
            worldSeries.name = worldSeriesName;
            worldSeries.useGeodata = true;
            worldSeries.exclude = excludedCountries;
            worldSeries.fillOpacity = 0.8;
            worldSeries.hiddenInLegend = true;
            worldSeries.mapPolygons.template.nonScalingStroke = true;

            polygonSeries.mapPolygons.template.events.on("over", function (event) {
                handleHover(event.target);
            });

            polygonSeries.mapPolygons.template.events.on("hit", function (event) {
                handleHover(event.target);
            });

            function handleHover(mapPolygon) {
                if (!isNaN(mapPolygon.dataItem.value)) {
                    heatLegend.valueAxis.showTooltipAt(mapPolygon.dataItem.value);
                } else {
                    heatLegend.valueAxis.hideTooltip();
                }
            }

            polygonSeries.mapPolygons.template.strokeOpacity = 0.4;
            polygonSeries.mapPolygons.template.events.on("out", function (event) {
                heatLegend.valueAxis.hideTooltip();
            });

            chart.zoomControl = new am4maps.ZoomControl();

            polygonSeries.data = JSON.parse(JSON.stringify(self.options.data));

        }
    });

    return $.mage.countryCharts;
});

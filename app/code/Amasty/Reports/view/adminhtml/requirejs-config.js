var config = {
    map: {
        '*': {
            'amreports_menu' : 'Amasty_Reports/js/amReportMenu',
            'amreports_toolbar' : 'Amasty_Reports/js/toolbar',
            'amreports_linear_charts' : 'Amasty_Reports/js/charts/linearCharts',
            'amreports_dashboard' : 'Amasty_Reports/js/dashboard',
            'amreports_simple_column_chart': 'Amasty_Reports/js/charts/simpleColumnChart',
            'amreports_simple_pie_chart': 'Amasty_Reports/js/charts/simplePieChart',
            'amreports_chart_country': 'Amasty_Reports/js/charts/chartMap',
            'amreports_chart_compare': 'Amasty_Reports/js/charts/chartMultiCompare',
            'amreports_multi_linear_chart': 'Amasty_Reports/js/charts/chartMultiValue',
            'amreports_multi_column_chart': 'Amasty_Reports/js/charts/multiColumnChart',
            'amreports_horizontal_column_chart': 'Amasty_Reports/js/charts/horizontalColumnChart',
            'amreports_tabs': 'Amasty_Reports/js/components/tabs'
        }
    },

    shim: {
        'es6-collections': {
            deps: ['Amasty_Reports/vendor/amcharts4/plugins/polyfill.min']
        },

        'Amasty_Reports/vendor/amcharts4/core': {
            deps: ['es6-collections']
        },

        'Amasty_Reports/vendor/amcharts4/charts': {
            deps: ['Amasty_Reports/vendor/amcharts4/core']
        },

        'Amasty_Reports/vendor/amcharts4/animated': {
            deps: ['Amasty_Reports/vendor/amcharts4/core']
        },

        'Amasty_Reports/vendor/amcharts4/maps': {
            deps: ['Amasty_Reports/vendor/amcharts4/core']
        },

        'Amasty_Reports/vendor/amcharts4/geodata/worldHigh': {
            deps: ['Amasty_Reports/vendor/amcharts4/core']
        }
    }
};

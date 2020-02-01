define([
    'underscore',
    'mageUtils',
    'uiLayout',
    'uiCollection'
    //'Magento_Ui/js/grid/columns/column'
], function (_, utils, layout, Collection) {
    'use strict';

    return Collection.extend({
        defaults: {
            headerTmpl: 'ui/grid/columns/text',
            bodyTmpl: 'Amasty_Ogrid/ui/grid/cells/items_ordered',
            disableAction: true,
            controlVisibility: true,
            sortable: true,
            sorting: false,
            visible: true,
            draggable: true,
            columns:{
                base: {
                    parent: '${ $.name }',
                    component: 'Magento_Ui/js/grid/columns/column',
                    bodyTmpl: 'Amasty_Ogrid/ui/grid/cells/text',
                    headerTmpl: 'Amasty_Ogrid/ui/grid/columns/text',
                    filter: 'text',
                    defaults: {
                        draggable: false,
                        sortable: false,
                    },
                    initObservable: function () {
                        this._super()
                            .track([
                                'visible',
                                'sorting',
                                'disableAction',
                                'subVisible',
                                'label'
                            ])
                            .observe([
                                'dragging'
                            ]);

                        return this;
                    },
                },
                thumbnail: {
                    component: 'Magento_Ui/js/grid/columns/thumbnail',
                    bodyTmpl: 'Amasty_Ogrid/ui/grid/cells/thumbnail',
                    has_preview: true
                }
            },
            imports: {
                productCols: '${ $.columnsControlsProvider }:productCols'
            },
            modules: {
                listingFilters: '${ $.listingFiltersProvider }'
            },
            listens: {
                productCols: 'updateProductCols',
                elems: 'updateFilters'
            }
        },
        initElement: function (el){
            el.track(['label', 'subVisible'])
        },
        initialize: function () {

            this._super();

            return this;
        },
        updateFilters: function(){
            _.each(this.elems(), function(column) {
                if (column.filter){
                    column.visible = column.subVisible;
                    var thisFilter = this.listingFilters().buildFilter(column);
                    layout([thisFilter]);
                }

            }.bind(this))
        },
        updateProductCols: function(){
            _.each(this.getVisibleCols(), function (col) {
                var config = utils.extend({}, this.columns.base, {
                    name: col.index,
                    subVisible: col.amogrid.visible,
                    visible: col.amogrid.visible,
                    label: col.amogrid.title,
                    filter: col.filter
                });

                if (col.productAttribute && col.frontendInput == 'media_image') {
                    config = utils.extend({}, config, this.columns.thumbnail);
                }
                var component = utils.template(config, {
                });

                layout([component]);

            }.bind(this));

            _.each(this.elems(), function(elem){
                _.each(this.productCols, function(col){
                    if (elem.index === col.index) {
                        elem.visible = col.amogrid.visible;
                        elem.subVisible = col.amogrid.visible;
                        elem.label = col.amogrid.title;
                    }
                })
            }.bind(this));
        },

        initObservable: function () {
            this._super()
                .track([
                    'visible',
                    'sorting',
                    'disableAction',
                    'productCols'
                ])
                .observe([
                    'dragging'
                ]);

            return this;
        },
        initFieldClass: function () {
            _.extend(this.fieldClass, {
                _dragging: this.dragging
            });

            return this;
        },
        getVisibleCols: function(){
            return _.filter(this.productCols, function(el){
                return el.amogrid.visible === true;
            });
        },
        getColumns: function(){
            return this.elems.filter('subVisible');
        },
        getItems: function(record){
            var rows = [];
            var orderData = record[this.index];

            return _.map(orderData);
        },
        getFieldClass: function () {},
        getHeader: function () {
            return this.headerTmpl;
        },
        getBody: function () {
            return this.bodyTmpl;
        },
        sort: function (enable) {},
        getFieldHandler: function () {}
    });

});
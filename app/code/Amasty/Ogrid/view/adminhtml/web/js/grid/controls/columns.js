define([
    'ko',
    'underscore',
    'mageUtils',
    'uiLayout',
    //'mageUtils',
    //'mage/translate',
    'Magento_Ui/js/grid/controls/columns',
], function(ko, _, utils, layout,/*l utils, $t, */uiColumns){
    'use strict';

    return uiColumns.extend({
        defaults: {
            selectedTab: 'general',
            template: 'Amasty_Ogrid/ui/grid/controls/columns',
            _tabs: [],
            _productCols: [],
            imports: {
                addTabs: '${ $.name }:tabsData',
                addProductColsData: '${ $.name }:productColsData',
                addDefaultColumnsData: '${ $.columnsProvider }:elems'
            },
            clientConfig: {
                component: 'Magento_Ui/js/grid/editing/client',
                name: '${ $.name }_client'
            },
            listens: {
                '${ $.storageConfig.provider }:activeView': 'activeView',
                '${ $.provider }:reloaded': 'gridReloaded'
            },
            modules: {
                client: '${ $.clientConfig.name }',
                source: '${ $.provider }'
            },
        },
        initialize: function () {
            _.bindAll(this, 'onDataSaved', 'onSaveError');

            this._super();

            layout([this.clientConfig]);

            return this;
        },
        initObservable: function () {
            this._super()
                .track(['selectedTab'])
                .observe({
                    tabs: [],
                    productCols: []
                });

            return this;
        },
        addTabs: function(tabs){
            _.map(tabs, function (value, key) {
                return utils.insert({
                    key: key,
                    value: value,
                    _parent: this,
                    visible: this.isVisibleTab
                }, this._tabs);
            }.bind(this))

            this._tabs = this._tabs.reverse();

            this.tabs(this._tabs);
        },
        hasSelected: function(tabKey){
            return this.selectedTab == tabKey;
        },
        addProductColsData: function(cols){
            _.map(cols, function (item, index) {
                item.index = index;
                return utils.insert(item, this._productCols);
            }.bind(this))

            this.productCols(this._productCols);
            this.initBookmarks(this._productCols);
        },
        addDefaultColumnsData: function(cols){
            this.initBookmarks(cols);
        },
        initBookmarks: function(cols){
            var initBookmarkColumns = function(columns){
                _.each(cols, function(column){

                    //var columns = view.data.columns;
                    if (columns[column.index] === undefined){
                        columns[column.index] = {
                            'sorting': false,
                            'visible': column.visible,
                            'amogrid_title': column.label
                        }
                    }
                })
            };

            initBookmarkColumns(this.storage().current.columns);
            _.each(this.storage().views, function(view){
                if (view.data){
                    initBookmarkColumns(view.data.columns);
                }
            });
        },
        isVisibleTab: function(){
            return this._parent.getColumns(this.key).length > 0;
        },
        getTabs: function() {
            return this.tabs.filter('visible');
        },
        getColumns: function(tab){
            var cols = [];

            if (tab === 'product'){
                cols = this.productCols();
            } else {
                cols = this.elems.filter(function(col){
                    var ret = false;
                    if (tab == 'unassigned' && !col.tab && col.index !== 'amasty_ogrid_items_ordered'){
                        ret = true;
                    } else if (col.tab == tab) {
                        ret = true;
                    }
                    return ret;
                });
            }
            return cols;
        },
        reloadBookmark: function(columns, elems){
            _.each(elems(), function(el){
                if (el.amogrid){
                    if (columns[el.index]){
                        columns[el.index].visible = el.amogrid.visible;
                        columns[el.index].amogrid_title = el.amogrid.title;
                    }
                }
            });
        },
        reloadCurrentBookmark: function(elems){
            var current = this.storage().current;

            this.reloadBookmark(current.columns, elems);

            if (this.storage().activeView.data){
                this.reloadBookmark(this.storage().activeView.data.columns, elems);
            }
        },
        save: function(){

            this.source().trigger('reload');

            var params = this.source().get('params');

            this.reloadCurrentBookmark(this.elems);
            this.reloadCurrentBookmark(this.productCols);

            var columns = this.storage().current.columns;
            var visible = {};

            for (var column in columns) {
                if (columns[column].visible === true) {
                    visible[column] = columns[column];
                }
            }

            this.storage().current.columns = visible;

            params.data = JSON.stringify({'current': this.storage().current});

            this.client()
                .save(params)
                .done(this.onDataSaved)
                .fail(this.onSaveError);
            this.showItemsOrderedColumn();
        },
        close: function(){
            this.storage().hasChanges = true;
        },
        onSaveError: function(){},
        onDataSaved: function(data){
            this.source().onReload(data['grid']);

            var data = this.source().get('params');
            data.data = null;

            this.showItemsOrderedColumn();
        },
        countVisible: function () {
            return this.elems.filter('visible').length + this.productCols.filter('visible').length;
        },
        getHeaderMessage: function () {
            return utils.template(this.templates.headerMsg, {
                visible: this.countVisible(),
                total: this.elems().length + this.productCols().length
            });
        },
        activeView: function(view){
            _.each(this.productCols(), function(el){
                if (view.data.columns[el.index] !== undefined){
                    if (view.data.columns[el.index].amogrid_title !== undefined) {
                        el.amogrid.title = view.data.columns[el.index].amogrid_title;
                    }
                    el.amogrid.visible = view.data.columns[el.index].visible;
                }
            });
            _.each(this.elems(), function(el){
                if (view.data.columns[el.index] !== undefined){
                    if (view.data.columns[el.index].amogrid_title !== undefined) {
                        el.amogrid.title = view.data.columns[el.index].amogrid_title;
                    }
                    el.amogrid.visible = view.data.columns[el.index].visible;
                }
            });
            this.save();
        },
        reloadGridState: function(elems){
            _.each(elems(), function(el){
                if (el.amogrid) {
                    el.visible = el.amogrid.visible;
                    el.label = el.amogrid.title;
                }
            })
        },
        gridReloaded: function(){
            this.reloadGridState(this.elems);
            this.reloadGridState(this.productCols);
            this.productCols(this.productCols());

            this.showItemsOrderedColumn();
            this.storage().hasChanges = true;
        },
        showItemsOrderedColumn: function(){
            var visibleColumns = _.filter(this.getColumns('product'), function(el){
                return el.amogrid.visible === true;
            })

            var cols = this.elems.filter(function(el){
                return el.index == 'amasty_ogrid_items_ordered';
            });

            if (cols[0]){
                cols[0].visible = visibleColumns.length > 0;
            }
        },
        initElement: function (el){
            el.track(['label'])
        }
    });
});

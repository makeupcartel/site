/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Checkoutspage v2.x.x
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
*/
define(
    [
        'jquery',
        'uiComponent',
        'Plumrocket_Checkoutspage/js/touchslider',
        'Plumrocket_Checkoutspage/js/model/size'        
    ],

    function ($, Component, slider,size) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Plumrocket_Checkoutspage/suggestion',
                showArrows: false
            },

            initObservable: function () {
                this._super()
                    .observe('showArrows');
                return this;
            },

            currentCount : 0,

            items : [],

            loadItems : function() {
                var self = this;
                if (!this.items.length) {
                    var i = 0,
                        _items = [];
                    $(this.itemSelector).each(function(){
                        if (i < self.getItemCount()) {
                            _items.push($(this).html());
                            i++;
                        } else {
                            self.items.push(_items);
                            _items = [$(this).html()];
                            i = 0;
                        }
                    });
                    self.items.push(_items);
                }
            },
        
            getItems : function() {
                if (!this.items.length) {
                    this.loadItems();
                    this._removeBaseContainer();
                }

                if (this.items.length > 1) {
                    this.showArrows(true);
                }
                return this.items;
            },

            _removeBaseContainer: function() {
                $('#slider-container').remove();
            },

            touchSlideRender : function() {
                $('#touchslider-container').touchSlider();
                
                var items = $('#touchslider-container').find('li'),
                    height = 0;
                $.each(items, function(value, key) {
                    if ($(this).height() > height) {
                        height = $(this).height();
                    }
                });
                $('.touchslider-viewport, #touchslider-items,#touchslider-container').height(height);
            },

            getItemCount : function() {
                if (!this.currentCount) {
                    var w = size.windowSize();
                    if (w >= 1025) {
                        this.currentCount = this.count[1024];
                    } else if (w < 1025 && w >= 640) {
                        this.currentCount = this.count[640];
                    } else {
                        this.currentCount = this.count[0];
                    }

                }
                return this.currentCount;
            }

        });
    }
);
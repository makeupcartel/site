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
    ['jquery'],
    function($) {
        "use strict";
        
        return {
            getScrollBarDimensions : function() {
                var elm = document.documentElement.offsetHeight ? document.documentElement : document.body,
                     curX = elm.clientWidth,
                     curY = elm.clientHeight,
                     hasScrollX = elm.scrollWidth > curX,
                     hasScrollY = elm.scrollHeight > curY,
                     prev = elm.style.overflow,
                     r = {
                        vertical: 0,
                        horizontal: 0
                     };

                if( !hasScrollY && !hasScrollX ) {
                    return r;
                }

                elm.style.overflow = "hidden";
                if( hasScrollY ) {
                    r.vertical = elm.clientWidth - curX;
                }

                if( hasScrollX ) {
                    r.horizontal = elm.clientHeight - curY;
                }
                elm.style.overflow = prev;

                return r;
            },

            windowSize : function() {
                var scroll = this.getScrollBarDimensions();
                return $( window ).width() + scroll.vertical;
            }
        }
    }
);

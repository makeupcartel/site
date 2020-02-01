require(["jquery", "mage/mage"], function ($) {
    $('#edit_form').mage("form", {
        handlersData: {
            saveAndRun: {
                action: {
                    args: {
                        start: 'ondemand',
                        back: 'edit'
                    }//start/ondemand/back/edit/
                }
            }
        }
    });
});

window.UnirgySortable = function (options) {
    if (!options.container) return;

    var $container = jQuery(options.container);
    $container.css({position: 'static'});
    var state, $curEl, tag = options.tag || 'li', filler, fillerPos;
    var mx, my, o, ox, oy, ow, oh, offset;

    jQuery(document).on('mousemove', move);
    jQuery(document).on('mouseup', drop);

    function move(ev) {
        if (state === 'mousedown') {
            options.ondrag && options.ondrag();
            state = 'dragging';
            $curEl.css({position: 'absolute', width: ow + 'px', height: oh + 'px', opacity: .8});
            filler = document.createElement(tag);
            jQuery(filler).css({height: oh + 'px'});
        } else if (state !== 'dragging') {
            return;
        }
        var nx = ev.pageX, ny = ev.pageY;

        var sy = jQuery(window).scrollTop(), so = 0, hh = 50;
        if (ny - sy < hh) {
            so = ny - sy - hh;
        } else {
            var vph = jQuery(window).height();
            if (ny - sy > vph - hh) {
                so = ny - sy - (vph - hh);
            }
        }
        if (so) window.scrollBy(0, so);

        //ox += nx-mx;
        oy += ny - my;
        mx = nx;
        my = ny;
        $curEl.css({left: ox + 'px', top: oy + 'px'});

        var els = $container.find(tag), i, el;
        for (i = 0; i < els.length; i++) {
            if (filler && els[i].offsetTop == filler.offsetTop || els[i].offsetTop == $curEl.offsetTop) continue;
            if (els[i].offsetTop > $curEl.offsetTop - offset.top) break;
            el = els[i];
        }
        if (el) {
            var $parent = jQuery(filler).parent();
            $parent.append(el);
            //Element.insert(el, {after: filler});
        } else {
            $container.prepend(filler);
            //Element.insert($container, {top: filler});
        }
    }

    function drop(ev) {
        if (!$curEl) return;
        if (state === 'dragging') {
            $curEl.css({position: '', left: '', top: '', width: '', height: '', opacity: 1});
            var $parent = $curEl.parent();
            $parent.append(filler);
            //Element.insert(filler, {after: $curEl});
            jQuery(filler).remove();
            options.ondrop && options.ondrop();
        }
        filler = null;
        state = null;
        $curEl = null;
    }

    return {
        drag: function (ev, el) {
            ev.stopPropagation();
            state = 'mousedown';
            $curEl = el.tagName === tag ? el : jQuery(el).closest(tag);
            mx = ev.pageX;
            my = ev.pageY;
            o = jQuery($curEl).position();
            ox = o.left;
            oy = o.top;
            //o = jQuery($curEl).getDimensions();
            ow = jQuery($curEl).width();
            oh = jQuery($curEl).height();
            offset = $container.position();
        }
    }
};

window.escapeHTML = function (stringParam) {
    return stringParam.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
};

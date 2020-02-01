/*global $, menuType */
require(['jquery',
    'jquery/ui',
    'mage/mage',
    'mage/translate'
], function ($) {
    jQuery = $;

    function stickyMenu(topval) {
        var isActive = (jQuery(window).scrollTop() > topval);
        if (isActive) {
            jQuery('.page-main-actions').addClass('_hidden');
            jQuery('.page-main-actions .page-actions').addClass('_fixed');
        } else {
            jQuery('.page-main-actions').removeClass('_hidden');
            jQuery('.page-main-actions .page-actions').removeClass('_fixed');
        }
    }

    function isActiveMenu() {
        if (jQuery('#is_active').prop('checked') == true) {
            jQuery('#is_active').val(1);
        } else {
            jQuery('#is_active').val(0);
        }
    }

    function reIntialize() {
        var updateOutput = function (e)
        {
            var list = e.length ? e : jQuery(e.target),
                    output = list.data('output');
            if (output) {
                if (window.JSON) {
                    output.val(window.JSON.stringify(list.nestable('serialize')));//, null, 2));
                } else {
                    output.val('JSON browser support required for Menu Items.');
                }
            }
            resetMenus();
        };

        // activate Nestable for list 1
        if (jQuery('#nestable .dd-item').length) {
            jQuery('#nestable').nestable({
                group: 1
            }).on('change', updateOutput);
            // output initial serialised data
            updateOutput(jQuery('#nestable').data('output', jQuery('#nestable-output')));
        } else {
            resetMenus();
        }

        jQuery('#nestable-menu').on('click', function (e)
        {
            var target = jQuery(e.target),
                    action = target.data('action');
            if (action === 'expand-all') {
                jQuery('.dd').nestable('expandAll');
            }
            if (action === 'collapse-all') {
                jQuery('.dd').nestable('collapseAll');
            }
        });
    }

    function getRandomInt(min, max) {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }

    function addColumns(currentRow, fields) {
        if (jQuery(currentRow).find('.blockselect')) {
            var columnsCount = 0;
            jQuery(currentRow).find('.blockselect').each(function () {
                var opt = jQuery(this).find(':selected');
                if (jQuery(this).parent().find('.showtitle').prop('checked')) {
                    var showTitleValue = 1;
                } else {
                    var showTitleValue = 0;
                }
                var value = opt.val();
                var og = opt.closest('optgroup').attr('data-column-type');
                var typetext = 'type';
                var valuetext = 'value';
                var showtitle = 'showtitle';
                fields += '<input type="hidden" class="hiddenItems" name="menu_data[' + menuId + '][item_columns][' + columnsCount + '][' + typetext + ']" value="' + og + '">';
                fields += '<input type="hidden" class="hiddenItems" name="menu_data[' + menuId + '][item_columns][' + columnsCount + '][' + valuetext + ']" value="' + value + '">';
                fields += '<input type="hidden" class="hiddenItems" name="menu_data[' + menuId + '][item_columns][' + columnsCount + '][' + showtitle + ']" value="' + showTitleValue + '">';
                columnsCount++;
            });
        }
        if (jQuery(currentRow).find('.blockselectcategory')) {
            var columnsCount = 0;
            jQuery(currentRow).find('.blockselectcategory').each(function () {
                var opt = jQuery(this).find(':selected');
                if (jQuery(this).parent().find('.showtitle').prop('checked')) {
                    var showTitleValue = 1;
                } else {
                    var showTitleValue = 0;
                }
                var position = jQuery(this).parent().siblings('.enable_blocks').attr('data-position');
                var enable = jQuery(this).parent().siblings('.enable_blocks').val();
                if (enable === "1") {
                    var value = jQuery(this).val();
                } else {
                    var value = "";
                }
                var enabletext = 'enable';
                var typetext = 'type';
                var valuetext = 'value';
                var showtitle = 'showtitle';
                fields += '<input type="hidden" class="hiddenItems" name="menu_data[' + menuId + '][category_columns][' + columnsCount + '][' + typetext + ']" value="' + position + '">';
                fields += '<input type="hidden" class="hiddenItems" name="menu_data[' + menuId + '][category_columns][' + columnsCount + '][' + enabletext + ']" value="' + enable + '">';
                fields += '<input type="hidden" class="hiddenItems" name="menu_data[' + menuId + '][category_columns][' + columnsCount + '][' + valuetext + ']" value="' + value + '">';
                fields += '<input type="hidden" class="hiddenItems" name="menu_data[' + menuId + '][category_columns][' + columnsCount + '][' + showtitle + ']" value="' + showTitleValue + '">';

                columnsCount++;
            });
        }


        return fields;
    }


    var totalMenus;
    var menuId = 0;
    function reOrderMenus(rootMenu) {
        var count = 1;
        var parentId = jQuery(rootMenu).attr('data-parentId');
        jQuery(rootMenu).children('.dd-item').each(function () {
            menuId++;
            var name = jQuery(this).attr('data-name');
            var type = jQuery(this).attr('data-type');
            var objectid = jQuery(this).attr('data-objectid');
            var link = jQuery(this).attr('data-link');
            var fontIcon = jQuery(this).attr('font-icon');
            var itemClass = jQuery(this).attr('item-class');
            var animationField = jQuery(this).attr('animation-field');
            if (animationField === "") {
                animationField = "bounceIn";
            }
            var cat = jQuery(this).attr('data-cat');
            if (cat === 'undefined') {
                cat = 0;
            }

            var verticalMenu = jQuery(this).attr('data-verticalmenu');
            if (verticalMenu === 'undefined') {
                verticalMenu = 0;
            }

            var verticalMenuBgColor = jQuery(this).attr('data-verticalmenubg');
            if (verticalMenuBgColor === 'undefined') {
                verticalMenuBgColor = 0;
            }
            //var cat = 'false';
            /*if(catValue === '1'){
             cat = "true";
             }else{
             cat = "false";
             }*/
            var fields = '<input type="hidden" class="hiddenItems" name="menu_data[' + menuId + '][item_id]" value="' + menuId + '"><input class="hiddenItems" type="hidden" name="menu_data[' + menuId + '][sort_order]" value="' + count + '"><input class="hiddenItems" type="hidden" name="menu_data[' + menuId + '][item_parent_id]" value="' + parentId + '"><input type="hidden" class="hiddenItems" name="menu_data[' + menuId + '][item_name]" value="' + name + '"><input type="hidden" class="hiddenItems" name="menu_data[' + menuId + '][item_type]" value="' + type + '"><input type="hidden" class="hiddenItems" name="menu_data[' + menuId + '][object_id]" value="' + objectid + '"><input type="hidden" class="hiddenItems" name="menu_data[' + menuId + '][item_link]" value="' + link + '"><input type="hidden" class="hiddenItems" name="menu_data[' + menuId + '][item_font_icon]" value="' + fontIcon + '"><input type="hidden" class="hiddenItems" name="menu_data[' + menuId + '][item_class]" value="' + itemClass + '"><input type="hidden" class="hiddenItems" name="menu_data[' + menuId + '][animation_option]" value="' + animationField + '"><input type="hidden" class="hiddenItems" name="menu_data[' + menuId + '][item_all_cat]" value="' + cat + '"><input type="hidden" class="hiddenItems" name="menu_data[' + menuId + '][item_vertical_menu]" value="' + verticalMenu + '"><input type="hidden" class="hiddenItems" name="menu_data[' + menuId + '][vertical_menu_bgcolor]" value="' + verticalMenuBgColor + '">';

            fields = addColumns(this, fields);
            jQuery(this).append(fields);
            if (jQuery(this).find('.dd-list').length) {
                var groupTag = jQuery(this).find('.dd-list');
                groupTag.attr('data-parentId', menuId);
                reOrderMenus(jQuery(this).find('.dd-list')[0]);
            }
            count++;
        });
        jQuery('.totalMenus').attr('value', menuId);
    }

    function resetMenus() {
        totalMenus = 0;
        menuId = 0;
        jQuery('.hiddenItems').remove();
        reOrderMenus(jQuery('.mainroot')[0]);
    }
    function menulabel(type) {
        var menuData = jQuery.parseJSON(menuTypes);
        return menuData[type];
    }
    /*For megamenu static block*/
    function generateMegaLiTag(id, name, type, objectId, link) {
        menuId++;
        var item = '<li class="dd-item col-m-12" data-objectid="' + objectId + '" data-link="' + link + '" data-id="' + id + '" data-name="' + name + '" data-type="' + type + '" font-icon="" item-class="" ><button class="cf removebtn btn right" href="#" type="button">Remove </button><a class="right collapse linktoggle">Collapse</a><a class="right expand linktoggle">Expand</a><div class="dd-handle">' + name + "<span class='right'>(" + menulabel(type) + ")</span>" + '</div><div class="item-information col-m-12"><div class="col-m-3"><h4  >Label</h4><input class="input-text admin__control-text required-entry linkclass linktypelabel" type="text" name="menu_data[' + menuId + '][mcustom_link_text]" value="' + name + '"></div><div class="col-m-3"><h4>Url</h4><input class="input-text admin__control-text validate-url linkclass linktypeurl" type="text" name="menu_data[' + menuId + '][custom_link_url]" value="' + link + '"></div><div class="col-m-3"><h4>Class</h4><input class="input-text admin__control-text linkclass linktypeclass" type="text" name="menu_data[' + menuId + '][item_class]" value=""></div><div class="col-m-3"><h4>Preceding Label Content</h4><input class="input-text admin__control-text linktypefont linkclass" type="text" name="menu_data[' + menuId + '][fonticon]" ><div class="admin__field-note"><span>This Content will be added before Menu Label.</span></div></div><div class="col-m-12 marginTop20 custColumnsBlock"><div class="col-m-4"><h4>Menu Columns </h4><select class="selectcolumns admin__control-select"><option value="1">One Column</option><option value="2">Two Columns</option><option value="3">Three Columns</option><option value="4">Four Columns</option><option value="5">Five Columns</option></select></div><div class="col-m-4"><h4>Animation Fields </h4>' + animationsFields + '</div><div class="col-m-12"><div class="menuColumnBlockWrapper"></div></div></div><div class="cf"></div></div></li>';
        return item;
    }
    /*For category link*/
    function generateLiTag(id, name, type, objectId, link, cat, verticalmenu, verticalmenubg) {

        menuId++;
        if (cat == '0') {
            var item = '<li class="dd-item col-m-12" data-objectid="' + objectId + '" data-link="' + link + '" data-verticalmenu="' + verticalmenu + '"   data-verticalmenubg="' + verticalmenubg + '" data-cat="' + cat + '" data-id="' + id + '" data-name="' + name + '" data-type="' + type + '" font-icon="" item-class="" animation-field="bounceIn"><button class="cf removebtn btn right" href="#" type="button">Remove </button><a class="right collapse linktoggle">Collapse</a><a class="right expand linktoggle">Expand</a><div class="dd-handle">' + name + "<span class='right'>(" + menulabel(type) + ")</span>" + '</div><div class="item-information col-m-12"><div class="col-m-3"><h4  >Label</h4><input class="input-text admin__control-text required-entry linkclass linktypelabel" type="text" name="menu_data[' + menuId + '][item_name]" value="' + name + '"></div><div class="col-m-3"><h4>Url</h4><input class="input-text admin__control-text linkclass linktypeurl" type="text" name="menu_data[' + menuId + '][custom_link_url]" value="' + link + '"><div class="admin__field-note"><span>Leave blank to link to home page URL.</span></div></div><div class="col-m-3"><h4>Class</h4><input class="input-text admin__control-text linkclass linktypeclass" type="text" name="menu_data[' + menuId + '][item_class]" value=""></div><div class="col-m-3"><h4>Preceding Label Content</h4><input class="input-text admin__control-text linktypefont linkclass" type="text" name="menu_data[' + menuId + '][fonticon]" ><div class="admin__field-note"><span>This Content will be added before Menu Label.</span></div></div>';
        } else {
            var item = '<li class="dd-item col-m-12" data-objectid="' + objectId + '" data-link="' + link + '" data-verticalmenu="' + verticalmenu + '" data-verticalmenubg="' + verticalmenubg + '" data-cat="' + cat + '" data-id="' + id + '" data-name="' + name + '" data-type="' + type + '" font-icon="" item-class="" animation-field="bounceIn"><button class="cf removebtn btn right" href="#" type="button">Remove </button><a class="right collapse linktoggle">Collapse</a><a class="right expand linktoggle">Expand</a><div class="dd-handle">' + name + "<span class='right'>(" + menulabel(type) + ")</span>" + '</div><div class="item-information col-m-12"><div class="col-m-4"><h4>Class</h4><input class="input-text admin__control-text linkclass linktypeclass" type="text" name="menu_data[' + menuId + '][item_class]" value=""></div><div class="col-m-4"><h4>Preceding Label Content</h4><input class="input-text admin__control-text linktypefont linkclass" type="text" name="menu_data[' + menuId + '][fonticon]" ><div class="admin__field-note"><span>This Content will be added before Menu Label.</span></div></div>';
        }


        if (menuType === '2') {
            if (type === "category") {
                item = item + '<div class="col-m-4"><h4>Animation Fields </h4>' + animationsFields + '</div><div class="cf"></div><div class="menuColumnBlockWrapper" style="margin:10px 0;"><div class="col-m-4 category_checkbox_wrapper"><input id="menu_data_' + menuId + '_subcat" class="admin__control-checkbox checkbox category_checkbox" type="checkbox" name="menu_data[' + menuId + '][subcat]" ><label for="menu_data_' + menuId + '_subcat" class="admin__field-label" style="line-height:16px;">Display all subcategories</label></div><div class="col-m-4 category_checkbox_wrapper"><input id="menu_data_' + menuId + '_verticalsubcat" class="admin__control-checkbox checkbox vertical_category_checkbox" type="checkbox" name="menu_data[' + menuId + '][verticalsubcat]" ><label for="menu_data_' + menuId + '_verticalsubcat" class="admin__field-label" style="line-height:16px;">Display Vertical Menu</label></div><div class="col-m-4 vertical_category_color_wrapper"><label for="menu_data_' + menuId + '_verticalcatcolor" class="admin__field-label" style="line-height:16px;">Vertical Menu Background Color</label><input id="menu_data_' + menuId + '_verticalcatcolor" class="jscolor admin__control-text vertical_category_color" type="text" name="menu_data[' + menuId + '][verticalcatcolor]" ></div></div><div class="cf"></div><div class="col-m-3"><h4>Header Block</h4><select data-position="header" class="enable_blocks admin__control-select" name="menu_data[' + menuId + '][header]" data-position="header"> <option value="0">No</option> <option value="1">Yes</option></select><div class="header_staticblock_select categorylink_category_select hidden" style="margin-top:10px;"><h4 style="margin:0;">Select Static Block</h4>' + menuCategorySelectStaticsBlock + '<p>Show Title <input type="checkbox" name="showtitle" class="showtitle"></p></div></div><div class="col-m-3"><h4>Left Block</h4><select class="enable_blocks admin__control-select" data-position="left" name="menu_data[' + menuId + '][left]"> <option value="0">No</option> <option value="1">Yes</option></select><div class="left_staticblock_select categorylink_category_select hidden" style="margin-top:10px;"><h4 style="margin:0;">Select Static Block</h4>' + menuCategorySelectStaticsBlock + '<p>Show Title <input type="checkbox" name="showtitle" class="showtitle"></p></div></div><div class="col-m-3"><h4>Right Block</h4><select class="enable_blocks admin__control-select" data-position="right" name="menu_data[' + menuId + '][right]"> <option value="0">No</option> <option value="1">Yes</option></select><div class="right_staticblock_select categorylink_category_select hidden" style="margin-top:10px;"><h4 style="margin:0;">Select Static Block</h4>' + menuCategorySelectStaticsBlock + '<p>Show Title <input type="checkbox" name="showtitle" class="showtitle"></p></div></div><div class="col-m-3"><h4>Bottom BLock</h4><select data-position="bottom" class="enable_blocks admin__control-select" name="menu_data[' + menuId + '][bottom]" data-position="bottom"> <option value="0">No</option> <option value="1">Yes</option></select><div class="bottom_staticblock_select categorylink_category_select hidden" style="margin-top:10px;"><h4 style="margin:0;">Select Static Block</h4>' + menuCategorySelectStaticsBlock + '<p>Show Title <input type="checkbox" name="showtitle" class="showtitle"></p></div></div>';
            }
        }
        item = item + '</div></li>';
        return item;
    }
    /*For static link*/
    function generateLiLinkTag(id, name, type, objectId, link) {
        menuId++;

        var item = '<li class="dd-item col-m-12" data-objectid="' + objectId + '" data-link="' + link + '" data-id="' + id + '" data-name="' + name + '" data-type="' + type + '" font-icon="" item-class=""><button class="cf removebtn btn right" href="#" type="button">Remove </button><a class="right collapse linktoggle">Collapse</a><a class="right expand linktoggle">Expand</a><div class="dd-handle">' + name + "<span class='right'>(" + menulabel(type) + ")</span>" + '</div><div class="item-information col-m-12"><div class="col-m-3"><h4>Label</h4><input class="input-text admin__control-text required-entry linkclass linktypelabel" type="text" name="menu_data[' + menuId + '][mcustom_link_text]" value="' + name + '"></div><div class="col-m-3"><h4>Url</h4><input class="input-text admin__control-text required-entry validate-url linkclass linktypeurl" type="text" name="menu_data[' + menuId + '][custom_link_url]" value="' + link + '"></div><div class="col-m-3"><h4>Class</h4><input class="input-text admin__control-text linkclass linktypeclass" type="text" name="menu_data[' + menuId + '][item_class]" value=""></div><div class="col-m-3"><h4>Preceding Label Content</h4><input class="input-text admin__control-text linktypefont linkclass" type="text" name="menu_data[' + menuId + '][fonticon]" ><div class="admin__field-note"><span>This Content will be added before Menu Label.</span></div></div><div class="cf"></div></div></li>';
        return item;
    }


    function toggleLi(obj) {
        if (jQuery(obj).hasClass('expand')) {
            jQuery(obj).hide();
            jQuery(obj).siblings('.collapse').show();
        } else {
            jQuery(obj).hide();
            jQuery(obj).siblings('.expand').show();
        }
        jQuery(obj).siblings('.item-information').slideToggle();
    }

    function createColumnsBlock(selectOptionObj) {
        var wrapper = jQuery(selectOptionObj).parents('.custColumnsBlock').find('.menuColumnBlockWrapper');
        wrapper.html('');

        var wrapperObj = jQuery(selectOptionObj).parents('.custColumnsBlock').find('.menuColumnBlockWrapper');
        var wrapperWidth = wrapperObj.width();
        var columnsCount = parseInt(jQuery(selectOptionObj).val());
        var columnMargin = 20;
        var perColumnWidth = (wrapperWidth / columnsCount) - (columnMargin);
        var blockHtml = '';
        for (var i = 1; i <= columnsCount; i++) {
            blockHtml += '<div class="menuColumnBlock column' + columnsCount + '">' + menuStaticsBlock + ' <p>Show Title <input type="checkbox" name="showtitle" class="showtitle"></p></div>';
        }
        jQuery(blockHtml).hide().appendTo(wrapper).fadeIn(1000);
    }

    jQuery(document).ready(function () {
        reIntialize();
        jQuery('.categoryAdd').click(function () {
            var catString = JSON.parse(jQuery('#product_categories').val());
            jQuery.each(catString, function (index, item) {
                var name = item.text.split('(')[0];
                var id = item.id;
                var type = 'category';
                var objectId = item.id;
                var link = '';
                var cat = "false";
                var verticalmenu = "false";
                var verticalmenubg = "FFFFFF";
                var item = generateLiTag(id, name, type, objectId, link, cat, verticalmenu, verticalmenubg);
                jQuery(item).hide().appendTo('#nestable .mainroot').fadeIn(1000);
            });
            jscolor.installByClassName("jscolor");
            jQuery('#product_categories').val('');
            jQuery('#product-categories input[type="checkbox"]').prop("checked", false);
            reIntialize();
        });

        jQuery('.pageAdd').click(function () {
            jQuery(this).parent().find('.addMenu').each(function () {
                if (jQuery(this).is(":checked")) {
                    var name = jQuery(this).data('name');
                    var id = jQuery(this).data('id');
                    var type = jQuery(this).data('type');
                    var objectId = jQuery(this).data('objectid');
                    var link = jQuery(this).data('url');
                    var item = generateLiTag(id, name, type, objectId, link, 0, 0, 0);
                    jQuery(item).hide().appendTo('#nestable .mainroot').fadeIn(1000);
                }
                jQuery(this).attr('checked', false);
            });
            reIntialize();
        });


        jQuery('.megaMenuBlockAdd').click(function () {

            var validate = true;
            jQuery('.megamenutext').each(function () {
                if (!jQuery(this).val()) {
                    jQuery(this).parent().find('.mage-error').remove();
                    jQuery(this).after('<label class="mage-error removeditem" generated="true">This is a required field.</label>');
                    validate = false;
                }
            });
            if (validate) {
                var name = jQuery('.megamenulabel').val();
                var id = '';
                var type = 'megamenu';
                var objectId = '';
                var link = jQuery(this).data('url');
                var item = "";
                var item = generateMegaLiTag(id, name, type, objectId, link);
                var newItem = jQuery(item).hide().appendTo('#nestable .mainroot').fadeIn(1000);
                createColumnsBlock(jQuery(newItem).find('.selectcolumns')[0]);
                jQuery('.megamenutext').each(function () {
                    jQuery(this).val('');
                });
                reIntialize();
            }
        });

        jQuery('.linkAdd').click(function () {
            var validate = true;
            jQuery('.linktext').each(function () {
                jQuery(this).parent().find('.mage-error').remove();
                if (!jQuery(this).val()) {
                    jQuery(this).after('<label class="mage-error removeditem" generated="true">This is a required field.</label>');
                    validate = false;
                } else if (jQuery(this).attr('type') == 'url') {
                    var regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/
                    if (!regexp.test(jQuery(this).val())) {
                        jQuery(this).after('<label class="mage-error removeditem" generated="true">Please enter a valid URL. Protocol is required (http://, https:// or ftp://).</label>');
                        validate = false;
                    }
                }
            });

            if (validate) {
                var link = jQuery('.linkurl').val();
                var name = jQuery('.linklabel').val();
                var id = '';
                var type = 'link';
                var objectId = '';
                var item = generateLiLinkTag(id, name, type, objectId, link);
                jQuery(item).hide().appendTo('#nestable .mainroot').fadeIn(1000);
                jQuery('.linktext').each(function () {
                    jQuery(this).val('');
                });
                reIntialize();
            }
        });

        jQuery('.linktext,.megamenutext').focus(function () {
            jQuery(this).parent().find('.removeditem').remove();
        });

        jQuery('.mainroot').on('click', '.removebtn', function () {
            jQuery(this).parent().closest('li.dd-item').remove();
            reIntialize();
        });

        jQuery('.mainroot').on('input', '.linkclass', function () {
            var currentObj = jQuery(this);
            if (currentObj.hasClass('linktypelabel')) {
                currentObj.parents('li.dd-item').attr('data-name', currentObj.val());
            } else if (currentObj.hasClass('linktypefont')) {
                var currentString = currentObj.val();
                currentString = currentString.replace(/"/g, "'");
                currentObj.parents('li.dd-item').attr('font-icon', currentString);
            } else if (currentObj.hasClass('linktypeclass')) {
                var currentString = currentObj.val();
                currentString = currentString.replace(/"/g, "'");
                currentObj.parents('li.dd-item').attr('item-class', currentString);
            } else {
                currentObj.parents('li.dd-item').attr('data-link', currentObj.val());
            }
            resetMenus();
        });

        jQuery('.mainroot').on('change', '.animationFields', function () {
            var currentObj = jQuery(this);
            currentObj.parents('li.dd-item').attr('animation-field', currentObj.val());
            resetMenus();
        });
        jQuery('.mainroot').on('change', '.category_checkbox', function () {
            var currentObj = jQuery(this);
            var catValue = 0;
            if (currentObj.prop('checked')) {
                var catValue = 1;
            }
            currentObj.parents('li.dd-item').attr('data-cat', catValue);
            resetMenus();
        });
        jQuery('.mainroot').on('change', '.vertical_category_checkbox', function () {
            var currentObj = jQuery(this);
            var catVerticalMenu = 0;
            if (currentObj.prop('checked')) {
                var catVerticalMenu = 1;
            }
            currentObj.parents('li.dd-item').attr('data-verticalmenu', catVerticalMenu);
            resetMenus();
        });
        
        jQuery('.mainroot').on('change', '.vertical_category_color', function () {
            var currentObj = jQuery(this);
            currentObj.parents('li.dd-item').attr('data-verticalmenubg', currentObj.val());
            resetMenus();
        });
        
        jQuery('.mainroot').on('change', '.enable_blocks', function () {
            var currentObj = jQuery(this);
            currentObj.parents('li.dd-item').attr('animation-field', currentObj.val());
            resetMenus();
        });


        jQuery('.mainroot').on('change', '.enable_blocks', function () {
            var currentObj = jQuery(this);
            //alert(currentObj.val());
            if (currentObj.val() === "1") {
                currentObj.siblings(".categorylink_category_select").removeClass('hidden');
            } else {
                currentObj.siblings(".categorylink_category_select").addClass('hidden');
            }

            //currentObj.parents('li.dd-item').attr('data-cat', currentObj.prop('checked'));
            //resetMenus();
        });

        jQuery('.linktoggle.collapse').hide();
        jQuery('.item-information').hide();

        jQuery('#nestable').on('click', '.linktoggle', function (event) {
            toggleLi(this);
        });

        jQuery('#nestable').on('change', '.selectcolumns', function () {
            createColumnsBlock(this);
        });

        jQuery('#nestable').on('change', '.blockselect', function () {
            //reIntialize();
            resetMenus();
        });
        jQuery('#nestable').on('change', '.showtitle', function () {
            //reIntialize();
            resetMenus();
        });

        /* sticky form menu */
        var topval = jQuery('.page-main-actions').position().top;
        jQuery(window).scroll(function () {
            stickyMenu(topval);
        });
        stickyMenu(topval);
        /* sticky form menu */

        /* Isactive enable disable */
        jQuery('#is_active').change(function () {
            isActiveMenu();
        });
        /* Isactive enable disable */
        /* save and validation */
        var dataForm = jQuery('#megamenu_form');
        dataForm.mage('validation');

        jQuery('.megamenu-save-continue').click(function () {
            jQuery('#menu_back').val('1');
            saveForm(jQuery);
        });
        jQuery('.megamenu-save').click(function () {
            jQuery('#menu_back').val('');
            saveForm(jQuery);
        });
        function serializeControls(dataForm) {
            var data = {};

            function buildInputObject(arr, val) {
                if (arr.length < 1)
                    return val;
                var objkey = arr[0];
                if (objkey.slice(-1) == "]") {
                    objkey = objkey.slice(0, -1);
                }
                var result = {};
                if (arr.length == 1) {
                    result[objkey] = val;
                } else {
                    arr.shift();
                    var nestedVal = buildInputObject(arr, val);
                    result[objkey] = nestedVal;
                }
                return result;
            }

            $.each(dataForm.serializeArray(), function () {
                var val = this.value;
                var c = this.name.split("[");
                var a = buildInputObject(c, val);
                $.extend(true, data, a);
            });

            return data;
        }


        function saveForm(jQuery) {

            if (dataForm.valid()) {
                console.log(dataForm.serializeArray());
                //alert('asdad');
                var serializeMenuData = JSON.stringify(serializeControls($('input[name^="menu_data\\["]')));

                dataForm.find('input[name^="menu_data\\["],select[name^="menu_data\\["]').attr('disabled', true);

                dataForm.append('<input type="hidden" name="menu_data_json" id="menu_data_json" value="">');
                jQuery('#menu_data_json').val(serializeMenuData);

                dataForm.submit();

            } else {
                jQuery('.mainroot').find('input.mage-error').each(function () {
                    var errorPlacement = jQuery(this);
                    var toggleObject = errorPlacement.parent().closest('li.dd-item').find('.expand.linktoggle');
                    toggleObject.hide();
                    toggleObject.siblings('.collapse').show();
                    toggleObject.siblings('.item-information').slideDown();
                    errorPlacement.parent().closest('li.dd-item').effect("shake");
                });
            }
        }
        /*save and validation  */
    });

});

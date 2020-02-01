/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    "jquery"
], function($){
    "use strict";

    $.widget('mage.awBlogWpImport', {
        options: {
            importButtonSelector: '[data-role=import-button]',
            canOverrideSelect: '#aw_blog_wordpress_import_can_override',
            importInputSelector: '',
            wpImportUrl: '',
            formKeySelector: 'input[name=form_key]'
        },

        /**
         * Initialize widget
         */
        _create: function() {
            this._bind();
        },

        /**
         * Event binding
         */
        _bind: function () {
            this._on({'click [data-role=import-button]': function () {this._sendForm();}});
        },

        /**
         * Send request
         */
        _sendForm: function () {
            var form = document.createElement("form"),
                fileInput = $(this.options.importInputSelector),
                formKeyInput = $(this.options.formKeySelector),
                canOverrideSelect = $(this.options.canOverrideSelect),
                canOverrideHiddenInput;

            canOverrideHiddenInput = $('<input>').attr('type','hidden').attr('name','can_override_posts');
            canOverrideHiddenInput.val(canOverrideSelect.val());

            form.setAttribute("method", 'post');
            form.setAttribute("enctype", "multipart/form-data");
            form.setAttribute("action", this.options.wpImportUrl);
            form.appendChild(fileInput[0]);
            form.appendChild(formKeyInput[0]);
            form.appendChild(canOverrideHiddenInput[0]);

            document.body.appendChild(form);
            form.submit();
        }
    });

    return $.mage.awBlogWpImport;
});
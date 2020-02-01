/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'ko',
    'jquery',
    'Magento_Ui/js/form/element/file-uploader',
    'mage/translate'
], function (ko, $, fileUploader, $t) {
    'use strict';

    return fileUploader.extend({
        defaults: {
            previewTmpl: 'Aheadworks_Blog/ui/form/element/image-preview',
            tooltipTpl: 'Aheadworks_Blog/ui/form/element/helper/html-tooltip',
            errorNotice: $t('Something went wrong while uploading the image')
        },

        /**
         * Initialize observable properties
         */
        initObservable: function () {
            this.imageUrl = ko.observable();
            this._super();
            var self = this;
            this.on('imageUrl',function(encodedUrl) {
                 if (encodedUrl) {
                     self.loadImageForPreview(encodedUrl);
                 }
            });
            return this;
        },

        /**
         * Replace encoded chars in the string
         *
         * @param {string} encodedString
         * @returns {*}
         */
        replaceEncodedChars: function(encodedString) {
            return encodedString
                .replace(new RegExp('-', 'g'), '+')
                .replace(new RegExp('_', 'g'), '/')
                .replace(new RegExp(',', 'g'), '=');
        },

        /**
         * Decode Url
         *
         * @param {string} encodedUrl
         * @returns {*}
         */
        decodeUrl: function (encodedUrl) {
            var urlParts,
                urlPart;

            urlParts = this.splitString(encodedUrl, '/');
            urlPart = this.findValueInArray(urlParts,'___directive');
            return atob(this.replaceEncodedChars(urlPart));
        },

        /**
         * Get image path
         *
         * @param {string} decodedUrl
         * @returns {*}
         */
        getImagePath: function (decodedUrl) {
            var urlParts;

            urlParts = this.splitString(decodedUrl, '"');
            return this.findValueInArray(urlParts, '{{media url=');
        },

        /**
         * Get static image path
         *
         * @param {string} url
         * @returns {*}
         */
        getStaticImagePath: function (url) {
            return url.split(location.host + '/media/').pop();
        },

        /**
         * Get image name
         *
         * @param {string} imagePath
         */
        getImageName: function (imagePath) {
            return this.splitString(imagePath, "/").last();
        },

        /**
         * Split any string into array using separator
         *
         * @param {string} url
         * @param {string} separator
         */
        splitString: function (url, separator) {
            return url.split(separator);
        },

        /**
         * Find value in array and return the value from the next index
         *
         * @param {string} array
         * @param {string} part
         * @returns {*}
         */
        findValueInArray: function (array, part) {
            return array[array.indexOf(part) + 1];
        },

        /**
         * Open Media Dialog form
         */
        openDialogForm: function () {
            MediabrowserUtility.openDialog(this.dialogUrl);
        },

        /**
         * On file remove handler
         */
        removeFile: function() {
            this._super();
            this.imageUrl('');
        },

        /**
         * Load image for preview
         *
         * @param {string} encodedUrl
         */
        loadImageForPreview: function (encodedUrl) {
            var error,
                self = this,
                file = {};

            try {
                if (encodedUrl.indexOf('___directive') === -1) {
                    file.path = this.getStaticImagePath(encodedUrl);
                    file.url = encodedUrl;
                    file.name = this.getImageName(encodedUrl);
                } else {
                    file.path = this.getImagePath(this.decodeUrl(encodedUrl));
                    file.url = this.mediaUrl + file.path;
                    file.name = this.getImageName(file.path);
                }
                self.addFile(file);
            } catch (error) {
                self.notifyError(this.errorNotice);
            }
        }
    });
});
/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'Magento_Ui/js/form/form',
    './adapter'
], function (Form, adapter) {
    'use strict';

    return Form.extend({
        /**
         * Initialize adapter
         * @returns {*}
         */
        initAdapter: function () {
            adapter.on({
                'reset': this.reset.bind(this),
                'save': this.save.bind(this, true, 'save'),
                'update': this.save.bind(this, true, 'update'),
                'schedule': this.save.bind(this, true, 'schedule'),
                'publish': this.save.bind(this, true, 'publish'),
                'saveAsDraft': this.save.bind(this, false, 'save_as_draft'),
                'saveAndContinue': this.save.bind(this, false, 'save_and_continue')
            });

            return this;
        },

        /**
         * Save action
         * @param {String} redirect
         * @param {String} action
         */
        save: function (redirect, action) {
            this.source.set('data.action', action);
            this._super(redirect);
        }
    });
});

/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

/**
 * Initialization custom date component
 *
 * @method initConfig()
 * @method onDateChange(value)
 * @method initObservable()
 */
define([
    'jquery',
    'Magento_Ui/js/form/element/date',
    'moment',
    'mage/translate'
], function ($, Abstract, moment, $t) {
    'use strict';

    return Abstract.extend({
        defaults: {
            saveAsDraftButton: '[data-ui-id=save-as-draft]',
            saveAndContinueButton: '[data-ui-id=save-and-continue-button]',
            updateButton: '[data-ui-id=update-button]',
            scheduleButton: '[data-ui-id=schedule-button]',
            publishButton: '[data-ui-id=publish-button]',
            defaultLabel: $t('Publish now'),
            publishLabel: $t('Publish at'),
            scheduleLabel: $t('Schedule for'),
            imports: {
                onValueChange: 'value'
            },
            listens: {
                'value': 'onDateChange'
            },
            comparisonPrecision: 'minutes',
            labelPropertyPath: 'label'
        },

        /**
         * Initializes observable properties for correct refreshing of the calendar label
         */
        initObservable: function () {
            this._super().track(this.labelPropertyPath);

            return this;
        },

        /**
         * Initialize config
         */
        initConfig: function () {
            this._super();
            this.allSaveButtons = [
                this.saveAsDraftButton,
                this.updateButton,
                this.scheduleButton,
                this.publishButton,
                this.saveAndContinueButton
            ].join(',');

            return this;
        },

        /**
         * Change elements visibility and calendar label
         */
        onDateChange: function (value) {

            $(this.allSaveButtons).hide();

            if (this.isPostAlreadyExists()) {
                $(this.saveAsDraftButton).show();
                $(this.saveAndContinueButton).show();
            }

            var date = new moment(value);

            if (date.isValid()) {
                if (this.isNeedToPublishPost(date)) {
                    this.updateLabel(this.publishLabel);
                    if (this.isPostAlreadyPublished()) {
                        $(this.updateButton).show();
                    } else {
                        $(this.publishButton).show();
                    }
                }
                if (this.isNeedToSchedulePost(date)) {
                    this.updateLabel(this.scheduleLabel);
                    if (this.isPostAlreadyScheduled()) {
                        $(this.updateButton).show();
                    } else {
                        $(this.scheduleButton).show();
                    }
                }
            } else {
                $(this.publishButton).show();
            }
        },

        /**
         * Check is current post already saved
         */
        isPostAlreadyExists: function () {
            return this.source.get('data.id');
        },

        /**
         * Check if selected date means post publication
         */
        isNeedToPublishPost: function (date) {
            return (
                moment().isAfter(date, this.comparisonPrecision)
                || moment().isSame(date, this.comparisonPrecision)
            );
        },

        /**
         * Check if selected date means post scheduling
         */
        isNeedToSchedulePost: function (date) {
            return moment().isBefore(date, this.comparisonPrecision);
        },

        /**
         * Update calendar label
         */
        updateLabel: function (labelValue) {
            this.set(this.labelPropertyPath, labelValue);
        },

        /**
         * Check if current post already saved as published
         */
        isPostAlreadyPublished: function () {
            return this.source.get('data.is_published');
        },

        /**
         * Check if current post already saved as scheduled
         */
        isPostAlreadyScheduled: function () {
            return this.source.get('data.is_scheduled');
        },

        /**
         * @inheritdoc
         */
        prepareDateTimeFormats: function () {
            this._super();
            if (this.options.showsTime && this.timezoneFormat) {
                this.validationParams.dateFormat = this.timezoneFormat;
            }
        }
    });
});
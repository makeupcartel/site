define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/components/fieldset'
], function (_, uiRegistry, fieldset) {
    'use strict';

    return fieldset.extend({

        /**
         * Show or Hide fieldset
         */
        doHideShow: function () {
            if (uiRegistry.get('amasty_groupcat_rule_form.amasty_groupcat_rule_form.rule_actions.links.hide_product').value() == 0
                || uiRegistry.get('amasty_groupcat_rule_form.amasty_groupcat_rule_form.rule_actions.restriction_action.allow_direct_links').value() == 1
            ) {
                this.visible(true);
            } else {
                this.visible(false);
            }
        }
    });
});

define([
    'jquery'
    ], function ($) {
    'use strict';
    
    return function (validator) {
        validator.addRule(
            'cvalidate-phone-number',
            function (value) {
                return /^[\+\d]?[0-9]{7}\d*$/.test(value);
            },
            $.mage.__('Please use only numbers (0-9) or plus sign (+) in this field and enter more or equal than 7 characters.')
        );
    
        return validator;
    };
});
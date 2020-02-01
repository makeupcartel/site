var config = {
    config: {
        'mixins': {
            'Magento_Checkout/js/action/update-shopping-cart': {
                'Convert_Checkout/js/action/update-shopping-cart-mixin':true
            },
            'Magento_Checkout/js/model/checkout-data-resolver': {
                'Convert_Checkout/js/model/checkout-data-resolver': true
            }
        }
    }
};
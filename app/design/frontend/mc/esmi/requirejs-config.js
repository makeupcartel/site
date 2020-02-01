var config = {
    map: {
        '*': {
            'owlCarousel':  'js/owl.carousel.min',
            'magnificPopup': 'js/jquery.magnific-popup.min',
            'scrollbar': 'js/jquery.scrollbar.min',
            'rangeslider' : 'js/rangeslider',
            'parally' : 'js/parally',
            'aos' : 'js/aos',
      'jqueryZoom': 'js/jquery.zoom.min'
        }
    },
    shim: {
        'js/owl.carousel.min': {
            'deps': ['jquery']
        },
        'js/jquery.scrollbar.min': {
            'deps': ['jquery']
        },
        'js/parally': {
            'deps': ['jquery']
        },
        'js/aos': {
            'deps': ['jquery']
        },
    'js/jquery.zoom.min': {
            'deps': ['jquery']
        }
    },
    config: {
        'mixins': {
            'Magento_Checkout/js/view/summary/abstract-total': {
                'js/modulecheckout/view/summary/abstract-total-mixin':true
            },
            'Magento_Checkout/js/view/shipping-information': {
                'js/modulecheckout/view/shipping-information-mixin':true
            },
            'Magento_Ui/js/lib/validation/validator': {
                'js/lib/validation/validator-mixin': true
            }
        }
    }
};

var config = {
    map: {
        '*': {
            'owlCarousel': 'js/owl.carousel.min',
            'swiper': 'js/swiper.min',
            'defaultJs': 'js/defaultjs.min',
            'scrollbar': 'js/jquery.scrollbar.min',
            'rangeslider': 'js/rangeslider',
            'jqueryZoom': 'js/jquery.zoom.min',
            'aos': 'js/aos'
        }
    },
    shim: {
        'js/owl.carousel.min': {
            'deps': ['jquery']
        },
        'js/swiper.min': {
            'deps': ['jquery']
        },
        'js/jquery.scrollbar.min': {
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
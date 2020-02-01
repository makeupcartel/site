define([
    'jquery',
    'uiComponent',
    'ShipperHQ_AddressAutocomplete/js/google_maps_loader',
    'Magento_Checkout/js/checkout-data' ,
    'uiRegistry'
], function (
    $,
    Component,
    GoogleMapsLoader,
    checkoutData,
    uiRegistry
) {

    var componentForm = {
        subpremise: 'short_name',
        street_number: 'short_name',
        route: 'long_name',
        locality: 'long_name',
        administrative_area_level_1: 'long_name',
        country: 'short_name',
        postal_code: 'short_name',
        postal_code_suffix: 'short_name',
        postal_town: 'short_name',
        sublocality_level_1: 'short_name'
    };

    var lookupElement = {
        street_number: 'street_1',
        route: 'street_2',
        locality: 'city',
        administrative_area_level_1: 'region',
        country: 'country_id',
        postal_code: 'postcode'
    };

    var googleMapError = false;
    window.gm_authFailure = function() {
        googleMapError = true;
    };
    var addressBilling;

    GoogleMapsLoader.done(function () {
        var enabled = window.checkoutConfig.shipperhq_autocomplete.active;
        
        var geocoder = new google.maps.Geocoder();
        setTimeout(function () {
            if(!googleMapError) {
                if (enabled == '1') {
                    var domID = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.street').elems()[0].uid,
                        countryDomID = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.country_id').uid;
                    addressBilling = setInterval(autoAddressBilling, 1000);
                    // Shipping address
                    var street = $('#' + domID);

                    //SHQ18-260
                    var observer = new MutationObserver(function () {
                        observer.disconnect();
                        $("#" + domID).attr("autocomplete", "new-password");
                    });

                    street.each(function () {
                        var element = this;

                        observer.observe(element, {
                            attributes: true,
                            attributeFilter: ['autocomplete']
                        });
                        if($('#'+countryDomID).val()){
                            autocomplete = new google.maps.places.Autocomplete(
                                /** @type {!HTMLInputElement} */(this),
                                {
                                    types: ['geocode'],
                                    componentRestrictions: {country: $('#'+countryDomID).val()}
                                }
                            );
                        }else{
                            autocomplete = new google.maps.places.Autocomplete(
                                /** @type {!HTMLInputElement} */(this),
                                {
                                    types: ['geocode'],
                                    componentRestrictions: {country: []}
                                }
                            );
                        }
                        
                        autocomplete.addListener('place_changed', fillInAddress);
                        $('#'+countryDomID).change(function () {
                            setAutocompleteCountry($(this));
                        });
                    });
                    $('#' + domID).focus(geolocate);
                    // End
                }
            }
        }, 5000);

    }).fail(function () {
        console.error("ERROR: Google maps library failed to load");
    });

    autoAddressBilling = function(){
        var paymentDomId = $('.opc-payment .payment-methods .payment-method');
        if(paymentDomId.length){
            var billingAddressDomID = $('.opc-payment .payment-methods .payment-method .payment-method-billing-address .checkout-billing-address > .fieldset input[name="street[0]"]');
            billingAddressDomID.each(function (i) {
                var domID = $(this).attr('id'), countryDomID = $(this).closest('[data-form="billing-new-address"]').find('select[name="country_id"]').attr('id');
                //SHQ18-260
                var observer = new MutationObserver(function () {
                    observer.disconnect();
                    $("#" + domID).attr("autocomplete", "new-password");
                });
                $('#'+domID).each(function () {
                    var element = this;
                    observer.observe(element, {
                        attributes: true,
                        attributeFilter: ['autocomplete']
                    });
                    if($('#'+countryDomID).val()){
                        var autocomplete2 = new google.maps.places.Autocomplete(
                            /** @type {!HTMLInputElement} */(this),
                            {
                                types: ['geocode'],
                                componentRestrictions: {country: $('#'+countryDomID).val()}
                            }
                        );
                    }else{
                        var autocomplete2 = new google.maps.places.Autocomplete(
                            /** @type {!HTMLInputElement} */(this),
                            {
                                types: ['geocode'],
                                componentRestrictions: {country: []}
                            }
                        );
                    }
                    autocomplete2.inputId = domID;
                    google.maps.event.addListener(autocomplete2, 'place_changed', function(){
                        fillInAddressBilling(autocomplete2, domID)
                    });
                    $('#'+countryDomID).change(function () {
                        if($(this).val()){
                            autocomplete2.setComponentRestrictions({'country': $(this).val()});
                        } else {
                            autocomplete2.setComponentRestrictions({'country': []});
                        } 
                    });
                })
                $('#' + domID).focus(geolocate);
            })
            clearInterval(addressBilling);
        }
    }

    var fillInAddress = function () {
        var domID = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.street').elems()[0].uid;
        var place = autocomplete.getPlace(),
            addressSuggest = $('#'+domID).val(),
            address = addressSuggest.split(',')[0];

        var street = [];
        var region  = '';
        var streetNumber = '';
        var city = '';
        var postcode = '';
        var postcodeSuffix = '';

        for (var i = 0; i < place.address_components.length; i++) {
            var addressType = place.address_components[i].types[0];
            if (componentForm[addressType]) {
                var value = place.address_components[i][componentForm[addressType]];
                if (addressType == 'subpremise') {
                    streetNumber = value + '/';
                } else if (addressType == 'street_number') {
                    streetNumber = streetNumber + value;
                } else if (addressType == 'route') {
                    street[1] = value;
                } else if (addressType == 'administrative_area_level_1') {
                    region = value;
                } else if (addressType == 'sublocality_level_1') {
                    city = value;
                } else if (addressType == 'postal_town') {
                    city = value;
                } else if (addressType == 'locality' && city == '') {
                    //ignore if we are using one of other city values already
                    city = value;
                } else if (addressType == 'postal_code') {
                    postcode = value;
                    var thisDomID = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.postcode').uid
                    if ($('#'+thisDomID)) {
                        $('#'+thisDomID).val(postcode + postcodeSuffix);
                        $('#'+thisDomID).trigger('change');
                        $('#' + thisDomID).addClass('locked');
                        $('label[for="' + thisDomID + '"]').addClass('locked');
                        if (!jQuery('.edit-field-btn[data-id="' + thisDomID + '"]').length) {
                            $('<span class="edit-field-btn" data-id="' + thisDomID + '">Edit</span>').insertAfter('label[for="' + thisDomID + '"]');
                        }
                    }
                } else if (addressType == 'postal_code_suffix' && window.checkoutConfig.shipperhq_autocomplete.use_long_postcode) {
                    postcodeSuffix = '-' + value;
                    var thisDomID = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.postcode').uid
                    if ($('#'+thisDomID)) {
                        $('#'+thisDomID).val(postcode + postcodeSuffix);
                        $('#'+thisDomID).trigger('change');
                        $('label[for="' + thisDomID + '"]').addClass('locked');
                        if (!jQuery('.edit-field-btn[data-id="' + thisDomID + '"]').length) {
                            $('<span class="edit-field-btn" data-id="' + thisDomID + '">Edit</span>').insertAfter('label[for="' + thisDomID + '"]');
                        }
                    }
                } else {
                    var elementId = lookupElement[addressType];
                    var thisDomID = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.'+ elementId).uid;
                    if ($('#'+thisDomID)) {
                        $('#'+thisDomID).val(value);
                        $('#'+thisDomID).trigger('change');
                    }
                }
            }
        }
        if (street.length > 0) {
            
            if(streetNumber){
                street[0] = streetNumber;
                var streetString = street.join(' ');
            }else{
                var streetString = address;
            }
            if ($('#'+domID)) {
                $('#'+domID).val(streetString);
                $('#'+domID).trigger('change');
            }
        }
        var cityDomID = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.city').uid;
        if ($('#'+cityDomID)) {
            $('#'+cityDomID).val(city);
            $('#'+cityDomID).trigger('change');
            $('#' + cityDomID).addClass('locked');
            $('label[for="' + cityDomID + '"]').addClass('locked');
            if (!jQuery('.edit-field-btn[data-id="' + cityDomID + '"]').length) {
                $('<span class="edit-field-btn" data-id="' + cityDomID + '">Edit</span>').insertAfter('label[for="' + cityDomID + '"]');
            }
        }
        if (region != '') {
            if (uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.region_id')) {
                var regionDomId = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.region_id').uid;
                if ($('#'+regionDomId)) {
                    //search for and select region using text
                    $('#'+regionDomId +' option')
                        .filter(function () {
                            return $.trim($(this).text()) == region;
                        })
                        .attr('selected',true);
                    $('#'+regionDomId).trigger('change');
                    $('#' + regionDomId).addClass('locked');
                    $('label[for="' + regionDomId + '"]').addClass('locked');
                    if (!jQuery('.edit-field-btn[data-id="' + regionDomId + '"]').length) {
                        $('<span class="edit-field-btn" data-id="' + regionDomId + '">Edit</span>').insertAfter('label[for="' + regionDomId + '"]');
                    }
                }
            }
            if (uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.region_id_input')) {
                var regionDomId = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.region_id_input').uid;
                if ($('#'+regionDomId)) {
                    $('#'+regionDomId).val(region);
                    $('#'+regionDomId).trigger('change');
                    $('#' + regionDomId).addClass('locked');
                    $('label[for="' + regionDomId + '"]').addClass('locked');
                    if (!jQuery('.edit-field-btn[data-id="' + regionDomId + '"]').length) {
                        $('<span class="edit-field-btn" data-id="' + regionDomId + '">Edit</span>').insertAfter('label[for="' + regionDomId + '"]');
                    }
                }
            }
        }
    }

    var fillInAddressBilling = function (autocomplete2, domID) {
        var place = autocomplete2.getPlace(),
            addressSuggest = $('#'+domID).val(),
            address = addressSuggest.split(',')[0];

        var street = [];
        var region  = '';
        var streetNumber = '';
        var city = '';
        var postcode = '';
        var postcodeSuffix = '';
        for (var i = 0; i < place.address_components.length; i++) {
            var addressType = place.address_components[i].types[0];
            if (componentForm[addressType]) {
                var value = place.address_components[i][componentForm[addressType]];
                if (addressType == 'subpremise') {
                    streetNumber = value + '/';
                } else if (addressType == 'street_number') {
                    streetNumber = streetNumber + value;
                } else if (addressType == 'route') {
                    street[1] = value;
                } else if (addressType == 'administrative_area_level_1') {
                    region = value;
                } else if (addressType == 'sublocality_level_1') {
                    city = value;
                } else if (addressType == 'postal_town') {
                    city = value;
                } else if (addressType == 'locality' && city == '') {
                    //ignore if we are using one of other city values already
                    city = value;
                } else if (addressType == 'postal_code') {
                    postcode = value;
                    var thisDomID = $('#'+domID).closest('[data-form="billing-new-address"]').find('[name="postcode"]').attr('id')
                    if ($('#'+thisDomID)) {
                        $('#'+thisDomID).val(postcode + postcodeSuffix);
                        $('#'+thisDomID).trigger('change');
                        $('#' + thisDomID).addClass('locked');
                        $('label[for="' + thisDomID + '"]').addClass('locked');
                        if (!jQuery('.edit-field-btn[data-id="' + thisDomID + '"]').length) {
                            $('<span class="edit-field-btn" data-id="' + thisDomID + '">Edit</span>').insertAfter('label[for="' + thisDomID + '"]');
                        }
                    }
                } else if (addressType == 'postal_code_suffix' && window.checkoutConfig.shipperhq_autocomplete.use_long_postcode) {
                    postcodeSuffix = '-' + value;
                    var thisDomID = $('#'+domID).closest('[data-form="billing-new-address"]').find('[name="postcode"]').attr('id')
                    if ($('#'+thisDomID)) {
                        $('#'+thisDomID).val(postcode + postcodeSuffix);
                        $('#'+thisDomID).trigger('change');
                        $('label[for="' + thisDomID + '"]').addClass('locked');
                        if (!jQuery('.edit-field-btn[data-id="' + thisDomID + '"]').length) {
                            $('<span class="edit-field-btn" data-id="' + thisDomID + '">Edit</span>').insertAfter('label[for="' + thisDomID + '"]');
                        }
                    }
                } else {
                    var elementId = lookupElement[addressType];
                    var thisDomID = $('#'+domID).closest('[data-form="billing-new-address"]').find('[name="'+elementId+'"]').attr('id');
                    if ($('#'+thisDomID)) {
                        $('#'+thisDomID).val(value);
                        $('#'+thisDomID).trigger('change');
                    }
                }
            }
        }
        if (street.length > 0) {
            if(streetNumber){
                street[0] = streetNumber;
                var streetString = street.join(' ');
            }else{
                var streetString = address;
            }
            $('#'+domID).val(streetString);
            $('#'+domID).trigger('change');
        }
        var cityDomID = $('#'+domID).closest('[data-form="billing-new-address"]').find('[name="city"]').attr('id');
        if ($('#'+cityDomID)) {
            $('#'+cityDomID).val(city);
            $('#'+cityDomID).trigger('change');
            $('#' + cityDomID).addClass('locked');
            $('label[for="' + cityDomID + '"]').addClass('locked');
            if (!jQuery('.edit-field-btn[data-id="' + cityDomID + '"]').length) {
                $('<span class="edit-field-btn" data-id="' + cityDomID + '">Edit</span>').insertAfter('label[for="' + cityDomID + '"]');
            }
        }
        if (region != '') {
            if ($('#'+domID).closest('[data-form="billing-new-address"]').find('[name="region_id"]').length) {
                var regionDomId = $('#'+domID).closest('[data-form="billing-new-address"]').find('[name="region_id"]').attr('id');
                if ($('#'+regionDomId)) {
                    //search for and select region using text
                    $('#'+regionDomId +' option')
                        .filter(function () {
                            return $.trim($(this).text()) == region;
                        })
                        .attr('selected',true);
                    $('#'+regionDomId).trigger('change');
                    $('#' + regionDomId).addClass('locked');
                    $('label[for="' + regionDomId + '"]').addClass('locked');
                    if (!jQuery('.edit-field-btn[data-id="' + regionDomId + '"]').length) {
                        $('<span class="edit-field-btn" data-id="' + regionDomId + '">Edit</span>').insertAfter('label[for="' + regionDomId + '"]');
                    }
                }
            }
            if ($('#'+domID).closest('[data-form="billing-new-address"]').find('[name="region"]').length) {
                var regionDomId = $('#'+domID).closest('[data-form="billing-new-address"]').find('[name="region"]').attr('id');
                if ($('#'+regionDomId)) {
                    $('#'+regionDomId).val(region);
                    $('#'+regionDomId).trigger('change');
                    $('#' + regionDomId).addClass('locked');
                    $('label[for="' + regionDomId + '"]').addClass('locked');
                    if (!jQuery('.edit-field-btn[data-id="' + regionDomId + '"]').length) {
                        $('<span class="edit-field-btn" data-id="' + regionDomId + '">Edit</span>').insertAfter('label[for="' + regionDomId + '"]');
                    }
                }
            }
        }
    }

    setAutocompleteCountry = function($this){
        if ($this.val()) {
            autocomplete.setComponentRestrictions({'country': $this.val()});
        } else {
            autocomplete.setComponentRestrictions({'country': []});
        }
    }

    geolocate = function () {
        if (navigator.geolocation && window.checkoutConfig.shipperhq_autocomplete.use_geolocation === '1') {
            navigator.geolocation.getCurrentPosition(function (position) {
                var geolocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                var circle = new google.maps.Circle({
                    center: geolocation,
                    radius: position.coords.accuracy
                });
                autocomplete.setBounds(circle.getBounds());
            });
        }
    }
    return Component;

});

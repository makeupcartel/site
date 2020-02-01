define([
    "jquery",
    "mage/translate",
    "jquery/ui"
], function ($, $t) {

    $.widget('mage.amLocator', {
        options: {},
        url: null,
        useBrowserLocation: null,
        useGeo: null,
        imageLocations: null,
        map: {},
        marker: {},
        amLatId: '',
        amLngId: '',
        needGoTo: false,

        _create: function () {
            this.ajaxCallUrl = this.options.ajaxCallUrl;
            this.useBrowserLocation = this.options.useBrowserLocation;
            this.useGeo = this.options.useGeo;
            this.imageLocations = this.options.imageLocations;
            this.amLatId = this.options.amLatId;
            this.amLngId = this.options.amLngId;
            this.Amastyload();
            var self = this;
            $("#" + this.options.nearbyButtonId).click(function() {
                self.needGoTo = true;
                self.navigateMe()
            });

            $("#" + this.options.searchButtonId).click(function() {
                self.makeAjaxCall()
            });

            $("#" + this.options.attributeFilterId).click(function() {
                self.makeAjaxCall()
            });
            if (automaticLocate) {
                self.needGoTo = true;
                self.navigateMe();
            }
            if (navigator.geolocation && self.useBrowserLocation == 1) {
                navigator.geolocation.getCurrentPosition( function(position) {
                    document.getElementById(self.options.amLatId).value = position.coords.latitude;
                    document.getElementById(self.options.amLngId).value = position.coords.longitude;
                });
            }
        },

        goHome: function(){
            window.location.href = window.location.pathname;
        },

        navigateMe: function(){
            if (navigator.geolocation) {
                var self = this;
                navigator.geolocation.getCurrentPosition( function(position) {
                    self.makeAjaxCall(position);
                }, this.navigateFail.bind(self));
            } else {
                alert($t('Sorry we\'re unable to display the nearby stores because ' +
                    'the "Use browser location" option is disabled in the module settings. ' +
                    'Please, contact the administrator.'));
            }
        },

        navigateFail: function(error) {
            // error param exists when user block browser location
            if (useGeoConfig == 1 && error.code == 1) {
                this.makeAjaxCall();
            }
        },

        getQueryVariable: function(variable) {
            var query = window.location.search.substring(1);
            var vars = query.split("&");
            for (var i=0;i<vars.length;i++) {
                var pair = vars[i].split("=");
                if (pair[0] == variable) {
                    return pair[1];
                }
            }
        },

        collectParams: function(position) {
            var lat = document.getElementById(this.options.amLatId).value;
            var lng = document.getElementById(this.options.amLngId).value;
            var currentAddress = document.getElementById(this.options.searchId).value;
            // using position data if current address is empty
            if ((!lat || !lng || !currentAddress) && (position != "" && typeof position!=="undefined")) {
                var lat = position.coords.latitude;
                var lng = position.coords.longitude;
            }

            var e = document.getElementById(this.options.searchRadiusId);
            var radius = e.options[e.selectedIndex].value;
            var form = $("#attribute-form").serialize();
            var params = {
                'lat' : lat,
                'lng' : lng,
                'radius' : radius,
                'mapId' : this.options.mapId,
                'storeListId' : this.options.storeListId,
                'product' : productId,
                'category' : categoryId,
                "attributes": form
            };

            return params;
        },

        makeAjaxCall: function(position) {
            var params = this.collectParams(position);
            var self = this;
            $.ajax({
                url     : this.ajaxCallUrl,
                type    : 'POST',
                data: params,
                showLoader: true
            }).done($.proxy(function(response) {
                response = JSON.parse(response);
                self.options.jsonLocations = response;
                self.Amastyload(response);
            }));
        },

        calculateDistance: function(lat, lng, measurement) {
            for (var location in this.options.jsonLocations.items) {
                var distance = MarkerClusterer.prototype.distanceBetweenPoints_(
                    new google.maps.LatLng(
                        lat,
                        lng
                    ),
                    new google.maps.LatLng(
                        this.options.jsonLocations.items[location]['lat'],
                        this.options.jsonLocations.items[location]['lng']
                    )
                );
                if (measurement == 'mi') {
                    distance = distance / 1.609344;
                }
                var locatorBlock = jQuery('#' + this.options.searchId).closest('#amasty_locator_block');
                var storeList = locatorBlock.find('.amlocator_store_list');
                var locationId = this.options.jsonLocations.items[location]['id'];
                var distanceText = '<div class="amasty_distance">Distance:' + parseInt(distance) + ' ' + measurement + '</div>';
                if (storeList.find('[data-amid=' + locationId + ']').find('div.amasty_distance').length > 0) {
                    storeList.find('[data-amid=' + locationId + ']').find('div.amasty_distance').html(distanceText);
                } else {
                    storeList.find('[data-amid=' + locationId + ']').append('<br />' + distanceText);
                }
            }
        },

        Amastyload: function(response) {
            this.initializeMap();
            this.processLocation(this.options.jsonLocations);

            if (enableClustering) {
                var markerCluster = new MarkerClusterer(this.map[this.options.mapId], this.marker[this.options.mapId], {imagePath: this.imageLocations+'/m'});
            }

            this.geocoder = new google.maps.Geocoder();

            if (this.options.showSearch) {
                var address = document.getElementById(this.options.searchId);
                var autocompleteOptions = {
                    componentRestrictions: {country: allowedCountries}
                };

                var autocomplete = new google.maps.places.Autocomplete(address, autocompleteOptions);
                google.maps.event.addListener(autocomplete, 'place_changed', function () {
                    var place = autocomplete.getPlace();
                    if (place.geometry != null) {
                        document.getElementById(self.amLatId).value = place.geometry.location.lat();
                        document.getElementById(self.amLngId).value = place.geometry.location.lng();
                        if (enableCountingDistance) {
                            var measurement = distanceConfig;
                            if ($('#amlocator-measurement').length > 0) {
                                measurement = $('#amlocator-measurement').val();
                            }
                            self.calculateDistance(place.geometry.location.lat(), place.geometry.location.lng(), measurement);
                        }
                    } else {
                        alert($t('You need to choose address from the dropdown with suggestions.'));
                    }
                });
            }

            var mapId = this.options.mapId;
            if (response && response.storeListId) {
                $("#" + response.storeListId).replaceWith(response.block);
                if (response.totalRecords > 0 && this.needGoTo) {
                    this.gotoPoint(response.items[0].id);
                    this.needGoTo = false;
                }
            }
            var self = this;
            $('[data-mapid=' +  mapId + ']').click(function(){
                var id =  $(this).attr('data-amid');
                self.gotoPoint(id);
            });

            $("#" + this.options.storeListId + " .today_schedule" ).click(function(event) {
                $(this).next( ".all_schedule" ).toggle( "slow", function() {
                    // Animation complete.
                });
                $(this).find( ".locator_arrow" ).toggleClass("arrow_active");
                event.stopPropagation();
            });
        },

        initializeMap: function() {
            this.infowindow = [];
            this.marker[this.options.mapId] = [];
            var myOptions = {
                zoom: 9,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                                styles: [{"elementType":"geometry","stylers":[{"color":"#f5f5f5"}]},{"elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"elementType":"labels.text.fill","stylers":[{"color":"#616161"}]},{"elementType":"labels.text.stroke","stylers":[{"color":"#f5f5f5"}]},{"featureType":"administrative.land_parcel","elementType":"labels.text.fill","stylers":[{"color":"#bdbdbd"}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#eeeeee"}]},{"featureType":"poi","elementType":"labels.text.fill","stylers":[{"color":"#757575"}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#e5e5e5"}]},{"featureType":"poi.park","elementType":"labels.text.fill","stylers":[{"color":"#9e9e9e"}]},{"featureType":"road","elementType":"geometry","stylers":[{"color":"#ffffff"}]},{"featureType":"road.arterial","elementType":"labels.text.fill","stylers":[{"color":"#757575"}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"color":"#dadada"}]},{"featureType":"road.highway","elementType":"labels.text.fill","stylers":[{"color":"#616161"}]},{"featureType":"road.local","elementType":"labels.text.fill","stylers":[{"color":"#9e9e9e"}]},{"featureType":"transit.line","elementType":"geometry","stylers":[{"color":"#e5e5e5"}]},{"featureType":"transit.station","elementType":"geometry","stylers":[{"color":"#eeeeee"}]},{"featureType":"water","elementType":"geometry","stylers":[{"color":"#c9c9c9"}]},{"featureType":"water","elementType":"labels.text.fill","stylers":[{"color":"#9e9e9e"}]}]
            
            };
            this.map[this.options.mapId] = [];
            this.map[this.options.mapId] = new google.maps.Map(document.getElementById(this.options.mapId), myOptions);
        },

        processLocation: function(locations) {
            var template = baloonTemplate.baloon; // document.getElementById("amlocator_window_template").innerHTML;
            var curtemplate = "";

            if (typeof locations.totalRecords == "undefined" || locations.totalRecords == 0){
                this.map[this.options.mapId].setCenter(new google.maps.LatLng(document.getElementById(this.options.amLatId).value, document.getElementById(this.options.amLngId).value));
                return false;
            }

            for (var i = 0; i < locations.totalRecords; i++) {

                curtemplate = template;
                curtemplate = curtemplate.replace("{{name}}", locations.items[i].name);
                curtemplate = curtemplate.replace("{{country}}", locations.items[i].country);
                curtemplate = curtemplate.replace("{{state}}", locations.items[i].state);
                curtemplate = curtemplate.replace("{{city}}", locations.items[i].city);
                curtemplate = curtemplate.replace("{{description}}", locations.items[i].description);
                curtemplate = curtemplate.replace("{{zip}}", locations.items[i].zip);
                curtemplate = curtemplate.replace("{{address}}", locations.items[i].address);
                curtemplate = curtemplate.replace("{{phone}}", locations.items[i].phone);
                curtemplate = curtemplate.replace("{{email}}", locations.items[i].email);
                curtemplate = curtemplate.replace("{{website}}", locations.items[i].website);
                curtemplate = curtemplate.replace("{{lat}}", locations.items[i].lat);
                curtemplate = curtemplate.replace("{{lng}}", locations.items[i].lng);
                curtemplate = curtemplate.replace("{{locatorPage}}", '<a href="' + locatorPageUrl + '?locationId=' + locations.items[i].id + '" target="_blank">' + widgetLinkText + '</a>');

                curtemplate = this.replaceIfStatement(curtemplate, locations.items[i].name,'name');
                curtemplate = this.replaceIfStatement(curtemplate, locations.items[i].country,'country');
                curtemplate = this.replaceIfStatement(curtemplate, locations.items[i].state,'state');
                curtemplate = this.replaceIfStatement(curtemplate, locations.items[i].city,'city');
                curtemplate = this.replaceIfStatement(curtemplate, locations.items[i].description,'description');
                curtemplate = this.replaceIfStatement(curtemplate, locations.items[i].zip,'zip');
                curtemplate = this.replaceIfStatement(curtemplate, locations.items[i].address,'address');
                curtemplate = this.replaceIfStatement(curtemplate, locations.items[i].phone,'phone');
                curtemplate = this.replaceIfStatement(curtemplate, locations.items[i].email,'email');
                curtemplate = this.replaceIfStatement(curtemplate, locations.items[i].website,'website');
                curtemplate = this.replaceIfStatement(curtemplate, locations.items[i].lat,'lat');
                curtemplate = this.replaceIfStatement(curtemplate, locations.items[i].lng,'lng');

                if (curtemplate) {
                    curtemplate = this.showAttributeInfo(curtemplate, locations.items[i], locations.currentStoreId);
                }

                if (locations.items[i].store_img != "") {
                    curtemplate = curtemplate.replace("{{photo}}",locations.items[i].store_img);
                } else {
                    curtemplate = curtemplate.replace(/<img[^>]*>/g,"");
                }
                if (locations.items[i].marker_img != "") {
                    markerImage = amMediaUrl + locations.items[i].marker_img;
                } else {
                    markerImage = "";
                }
                this.createMarker(locations.items[i].lat, locations.items[i].lng,  curtemplate, markerImage, locations.items[i].id);
            }
            var bounds = new google.maps.LatLngBounds();
            for (var locationId in this.marker[this.options.mapId]) {
                bounds.extend(this.marker[this.options.mapId][locationId].getPosition());
            }

            var self = this;
            this.map[this.options.mapId].fitBounds(bounds);
            if (locations.totalRecords == 1 || self.needGoTo) {
                google.maps.event.addListenerOnce(this.map[this.options.mapId], 'bounds_changed', function() {
                    self.map[self.options.mapId].setZoom(mapZoom);
                });
            }

            var activeLocation = this.options.activeLocation;
            if (activeLocation) {
                google.maps.event.addListenerOnce(this.map[this.options.mapId], 'tilesloaded', function() {
                    self.gotoPoint(activeLocation);
                });
            }
        },

        showAttributeInfo: function (curtemplate, item, currentStoreId) {
            var attributeTemplate = baloonTemplate.attributeTemplate;
            if (item.attributes) {
                for (var j = 0; j < item.attributes.length; j++) {
                    var label = item.attributes[j].frontend_label;
                    var labels = item.attributes[j].labels;
                    if (labels[currentStoreId]) {
                        label = labels[currentStoreId];
                    }

                    var value = item.attributes[j].value;
                    if (item.attributes[j].boolean_title) {
                        value = item.attributes[j].boolean_title;
                    }
                    if (item.attributes[j].option_title) {
                        var optionTitles = item.attributes[j].option_title;
                        value = '<br>';
                        for (var k = 0; k < optionTitles.length; k++) {
                            value += '- ';
                            if (optionTitles[k][currentStoreId]) {
                                value += optionTitles[k][currentStoreId];
                            } else {
                                value += optionTitles[k][0];
                            }
                            value += '<br>';
                        }
                    }
                    if (attributeTemplate) {
                        attributeTemplate = attributeTemplate.replace("{{title}}",label);
                        curtemplate += attributeTemplate.replace("{{value}}",value);
                    }

                    attributeTemplate = baloonTemplate.attributeTemplate;
                }
            }
            return curtemplate;
        },

        gotoPoint: function(myPoint) {
            var mapId = this.closeAllInfoWindows();
            $('[data-mapid=' +  mapId + ']').removeClass('active');
            // add class if click on marker
            $('[data-mapid=' +  mapId + '][data-amid=' + myPoint + ']').addClass('active');
            this.map[mapId].setCenter(
                new google.maps.LatLng(
                    this.marker[mapId][myPoint].position.lat(),
                    this.marker[mapId][myPoint].position.lng()
                )
            );
            this.map[mapId].setZoom(mapZoom);
            this.marker[mapId][myPoint]['infowindow'].open(
                this.map[mapId],
                this.marker[mapId][myPoint]
            );
        },

        replaceIfStatement: function(text,value,template){
            var patt = new RegExp("\{\{if"+template+"\}\}([\\s\\S]*)\{\{\/\if"+template+"}\}","g");
            var cuteText = patt.exec(text);
            if (cuteText!=null ){
                if (value=="" || value==null){
                    text = text.replace(cuteText[0], '');
                }else{
                    var finalText = cuteText[1].replace('{{'+template+'}}', value);
                    text = text.replace(cuteText[0], finalText);
                }

                return text;
            }
            return text;
        },

        createMarker: function(lat, lon, html, marker, locationId) {
            var image = marker.split('/').pop();
            if (marker && image != 'null') {
                var marker = {
                    url: marker,
                    size: new google.maps.Size(48, 48),
                    scaledSize: new google.maps.Size(48, 48)
                };
                var newmarker = new google.maps.Marker({
                    position: new google.maps.LatLng(lat, lon),
                    map: this.map[this.options.mapId],
                    icon: marker
                });
            } else {
                var newmarker = new google.maps.Marker({
                    position: new google.maps.LatLng(lat, lon),
                    map: this.map[this.options.mapId]
                });
            }

            newmarker['infowindow'] = new google.maps.InfoWindow({
                content: html
            });
            newmarker['locationId'] = locationId;
            var self = this;
            google.maps.event.addListener(newmarker, 'click', function() {
                self.gotoPoint(this.locationId);
            });

            // using locationId instead 0, 1, 2, i counter
            this.marker[this.options.mapId][locationId] = newmarker;
        },

        closeAllInfoWindows: function () {
            var mapId = this.element.context.id;
            var spans = document.getElementById(mapId).getElementsByTagName('span');

            for(var i = 0, l = spans.length; i < l; i++){
                spans[i].className = spans[i].className.replace(/\active\b/,'');
            }

            if (typeof this.marker[mapId] !=="undefined") {
                for (var marker in this.marker[mapId]) {
                    if (this.marker[mapId].hasOwnProperty(marker)) {
                        this.marker[mapId][marker]['infowindow'].close();
                    }
                }
            }

            return mapId;
        },

    });

    return $.mage.amLocator;
});

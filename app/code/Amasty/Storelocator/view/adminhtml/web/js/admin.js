define([
    "jquery",
    "jquery/ui"
], function ($) {



    $.widget('mage.amLocator', {
        options: {},
        apiKey: null,
        markers: [],
        

        _create: function () {
            this.apiKey = this.options.apiKey;

            var script = document.createElement('script');
            script.type = 'text/javascript';
            script.src = 'https://maps.googleapis.com/maps/api/js?v=3.17.exp' +this.apiKey;
            document.body.appendChild(script);

            
            var self = this;
            $('#amasty_storelocator_location_edit_tabs_map').click(function() {
                self.displayByLatLng();
            });

            $('#location_lat').keyup(function() {
                document.getElementById("location_lat").value = document.getElementById("location_lat").value.replace(",",".");
                self.deleteMarkers();
                self.displayByLatLng();
            });


            $('#amlocator_fill').click(function() {
                self.display();
            });
            
            $('#location_lng').keyup(function() {
                document.getElementById("location_lng").value = document.getElementById("location_lng").value.replace(",",".");
                self.deleteMarkers();
                self.displayByLatLng();
            });
        },

        displayByLatLng: function(){
            document.getElementById("map-canvas").style.display = "block";
            var mapOptions = {
                zoom: 4
            };

            if (!this.map)
                this.map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
            var lat = document.getElementById('location_lat').value;
            var lng = document.getElementById('location_lng').value;
            if (document.getElementById('location_marker_img').getAttribute('value')) {
                var markerImage = document.getElementById('marker_img').getAttribute('src');
            }
            if ( lat!="" && lng!="" && markerImage) {
                var myLatlng = new google.maps.LatLng(lat, lng);
                var markerImage = {
                    url: markerImage,
                    size: new google.maps.Size(48, 48),
                    scaledSize: new google.maps.Size(48, 48)
                };
                var marker = new google.maps.Marker({
                    map: this.map,
                    position: myLatlng,
                    icon: markerImage
                });
                this.markers.push(marker);
                this.map.setCenter(myLatlng);
            } else if (!markerImage) {
                var myLatlng = new google.maps.LatLng(lat, lng);
                var marker = new google.maps.Marker({
                    map: this.map,
                    position: myLatlng
                });
                this.markers.push(marker);
                this.map.setCenter(myLatlng);
            } else {
                var myLatlng = new google.maps.LatLng(-34.397,150.644);
                this.map.setCenter(myLatlng);
            }
            return true;
        },

        deleteMarkers: function() {
            for (var i = 0; i < this.markers.length; i++) {
                this.markers[i].setMap();
            }
            this.markers = [];
        },

        display: function(){
            var e = document.getElementById("location_country");
            var country = e.options[e.selectedIndex].text;

            var city = document.getElementById('location_city').value;
            var zip = document.getElementById('location_zip').value;
            var address = document.getElementById('location_address').value;

            address = country +','+ city+','+zip+','+address;

            geocoder = new google.maps.Geocoder();
            document.getElementById("map-canvas").style.display = "block";
            var mapOptions = {
                zoom: 4
            };

            if (!this.map)
                this.map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);


            self = this;
            this.deleteMarkers();
            geocoder.geocode( { 'address': address}, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    self.map.setCenter(results[0].geometry.location);
                    document.getElementById('location_lat').value = results[0].geometry.location.lat();
                    document.getElementById('location_lng').value = results[0].geometry.location.lng();
                    var marker = new google.maps.Marker({
                        map: self.map,
                        position: results[0].geometry.location
                    });
                    self.markers.push(marker);

                }else{
                    self.displayByLatLng();
                }
            });
        }
    });
    return $.mage.amLocator;
});

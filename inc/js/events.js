

jQuery(document).ready(function () {

	jQuery('#event-date').datepicker({
		//dateFormat: 'mm/dd/yy'
		dateFormat: 'DD, MM d, yy'
	});

	jQuery('#event-time').timepicker({
		showPeriod: true,
		showLeadingZero: false
	})

});

function initialize() {
	var input = document.getElementById('event-location');
	var autocomplete = new google.maps.places.Autocomplete(input);
	google.maps.event.addListener(autocomplete, 'place_changed', function () {
		var place = autocomplete.getPlace();
		console.log(place);
		var lat = place.geometry.location.lat();
		var lng = place.geometry.location.lng();
		var latlng = lat + ',' + lng;
		//document.getElementById('event-place').value = JSON.stringify(place);
		if( place.formatted_address.indexOf( place.name ) > -1 )
			document.getElementById('event-address').value = place.formatted_address;
		else
			document.getElementById('event-address').value = place.name + ', ' + place.formatted_address;
		document.getElementById('event-latlng').value = latlng;
	});
}

google.maps.event.addDomListener(window, 'load', initialize);


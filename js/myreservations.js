$(document).ready(function() {
	
	// Fill by default with Canadian provinces:
	var countryLabel = $('#country').val();
	
	$.get('stategenerator.php?country=' + countryLabel, function(data, status) {
		$('#state').html(data);
	});
	
	switch (countryLabel) {
		case 'CAN':
			$('#state-label').text('Province:');
			$('#state').prop('disabled', false);
			break;
		case 'USA':
			$('#state-label').text('State:');
			$('#state').prop('disabled', false);
			break;
		default:
			$('#state').prop('disabled', true);
			break;
	}
	
	// When country changes:
	$('#country').change(function() {
		var selectedCountry = $('#country').val();
		
		// If country selected is Canada or U.S., populate states
		if (selectedCountry == 'CAN') {
			$('#state-label').text('Province:');
			$('#state').prop('disabled', false);
			
			// USE AJAX TO POPULATE WITH CANADIAN PROVINCES
			$.get('stategenerator.php?country=CAN', function(data, status) {
				$('#state').html(data);
			});
		}
		
		else if (selectedCountry == 'USA') {
			$('#state-label').text('State:');
			$('#state').prop('disabled', false);
			
			// USE AJAX TO POPULATE WITH U.S. STATES
			$.get('stategenerator.php?country=USA', function(data, status) {
				$('#state').html(data);
			});
		}
		
		// Else, make sure 'state' is grayed out
		else {
			$('#state').prop('disabled', true);
		}
	});
});
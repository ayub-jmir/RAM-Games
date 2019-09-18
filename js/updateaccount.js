$(document).ready(function() {
	var selectedCountry = $('#country').val();
	var selectedState = $('#stateid').val();
	
	if (selectedCountry == 'CAN') {
		$('#state-label').text('Province:');
		$('#state').prop('disabled', false);
		
		// USE AJAX TO POPULATE WITH CANADIAN PROVINCES
		$.get('stategenerator.php?country=CAN&state=' + selectedState, function(data, status) {
			$('#state').html(data);
		});
	}
	
	else if (selectedCountry == 'USA') {
		$('#state-label').text('State:');
		$('#state').prop('disabled', false);
		
		// USE AJAX TO POPULATE WITH U.S. STATES
		$.get('stategenerator.php?country=USA&state=' + selectedState, function(data, status) {
			$('#state').html(data);
		});
	}
	
	// Else, make sure 'state' is grayed out
	else {
		$('#state').html('');
		$('#state').prop('disabled', true);
	}

	$('#country').change(function() {
		var selectedCountry = $('#country').val();
		var selectedState = $('#stateid').val();

		// If country selected is Canada or U.S., populate states
		if (selectedCountry == 'CAN') {
			$('#state-label').text('Province:');
			$('#state').prop('disabled', false);
			
			// USE AJAX TO POPULATE WITH CANADIAN PROVINCES
			$.get('stategenerator.php?country=CAN&state=' + selectedState, function(data, status) {
				$('#state').html(data);
			});
		}
		
		else if (selectedCountry == 'USA') {
			$('#state-label').text('State:');
			$('#state').prop('disabled', false);
			
			// USE AJAX TO POPULATE WITH U.S. STATES
			$.get('stategenerator.php?country=USA&state=' + selectedState, function(data, status) {
				$('#state').html(data);
			});
		}
		
		// Else, make sure 'state' is grayed out
		else {
			$('#state').html('');
			$('#state').prop('disabled', true);
		}
	});
});
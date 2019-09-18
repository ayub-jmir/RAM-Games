$(document).ready(function() {
	var selectedCountry = $('#countryid').val();
	var selectedState = $('#stateid').val();
	// Fill by default with Canadian provinces:

	if (selectedCountry == 'CAN') {
			$('#state-label').text('Province:');
			$('#state').prop('disabled', false);
			
			// USE AJAX TO POPULATE WITH CANADIAN PROVINCES
			$.get('stategenerator.php?state=' + selectedState +'&country=CAN&', function(data, status) {
				$('#state').html(data);
			});
		}
		
		else if (selectedCountry == 'USA') {
			$('#state-label').text('State:');
			$('#state').prop('disabled', false);
			
			// USE AJAX TO POPULATE WITH U.S. STATES
			$.get('updatestategenerator.php?state=' + selectedState +'&country=USA&', function(data, status) {
				$('#state').html(data);
			});
		}
		
		// Else, make sure 'state' is grayed out
		else {
			$('#state').html('');
			$('#state').prop('disabled', true);
		}
	});
	
// When country changes:
$('#country').change(function() {
	var selectedCountry = $('#countryid').val();
	var selectedState = $('#stateid').val();
	
	// If country selected is Canada or U.S., populate states
	if (selectedCountry == 'CAN') {
		$('#state-label').text('Province:');
		$('#state').prop('disabled', false);
		
		// USE AJAX TO POPULATE WITH CANADIAN PROVINCES
		$.get('updatestategenerator.php?state=' + selectedState +'&country=CAN&', function(data, status) {
			$('#state').html(data);
		});
	}
	
	else if (selectedCountry == 'USA') {
		$('#state-label').text('State:');
		$('#state').prop('disabled', false);
		
		// USE AJAX TO POPULATE WITH U.S. STATES
		$.get('stategenerator.php?state=' + selectedState +'&country=USA&', function(data, status) {
			$('#state').html(data);
		});
	}
	
	// Else, make sure 'state' is grayed out
	else {
		$('#state').html('');
		$('#state').prop('disabled', true);
	}
});

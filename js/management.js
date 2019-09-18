$(document).ready(function() {
	
	// Populate game name descriptions
	var gameId = $('#game-name-description-select').val();
	var gameId2 = $('#game-platform-add-game').val();
	
	$.get('gamedescriptiongenerator.php?gameId=' + gameId, function(data, status) {
		$('#game-info-description').val(data);
	});
	
	// Populate game prices
	$.get('gameplatformpricegenerator.php?gameId=' + gameId, function(data, status) {
		$('#game-price-table tbody').html(data);
		$('#gameId').val(gameId);
	});
	
	// Populate game platform add form
	$.get('gameplatformaddgenerator.php?gameId=' + gameId2, function(data,status) {
		$('#game-platform-add-platform').html(data);
		$('#gameId2').val(gameId2);
	});
	
	// Fill by default with Canadian provinces:
	$.get('stategenerator.php?country=CAN', function(data, status) {
		$('#state').html(data);
	});
	
	// Functions
	$('#game-name-description-select').change(function() {
		var gameId = $('#game-name-description-select').val();
	
		$.get('gamedescriptiongenerator.php?gameId=' + gameId, function(data, status) {
			$('#game-info-description').val(data);
		});
		
		$.get('gameplatformpricegenerator.php?gameId=' + gameId, function(data, status) {
			$('#game-price-table tbody').html(data);
			$('#gameId').val(gameId);
		});
	});
	
	$('#game-platform-add-game').change(function() {
		var gameId2 = $('#game-platform-add-game').val();
		
		$.get('gameplatformaddgenerator.php?gameId=' + gameId2, function(data,status) {
			$('#game-platform-add-platform').html(data);
			$('#gameId2').val(gameId2);
		});
	});
	
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
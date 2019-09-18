$(document).ready(function() {
	
	// Populate game name descriptions
	var platform = $('#platform').val(), genre = $('#genre').val(), name = $('#name').val();
	
	$.get('editgamegenerator.php?platform=' + platform + '&genre=' + genre + '&name=' + name, function(data, status) {
		$('#game-content').html(data);
	});
	
	$('#genre').change(function() {
		genre = $('#genre').val()
		$.get('editgamegenerator.php?platform=' + platform + '&genre=' + genre + '&name=' + name, function(data, status) {
			$('#game-content').html(data);
		});
	});
	
	$('#platform').change(function() {
		platform = $('#platform').val();
		$.get('editgamegenerator.php?platform=' + platform + '&genre=' + genre + '&name=' + name, function(data, status) {
			$('#game-content').html(data);
		});
	});
	
	$('#name').keyup(function() {
		name = $('#name').val();
		$.get('editgamegenerator.php?platform=' + platform + '&genre=' + genre + '&name=' + name, function(data, status) {
			$('#game-content').html(data);
		});		
	});
	
	/*
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
	
	*/
});
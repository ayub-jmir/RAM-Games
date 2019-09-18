$(document).ready(function() {
	
	// On submission of search form
	$('#submit-search').click(function() {
				
		$('#game-form').hide();
		$('#game-content').show();
		
		// Ordering parameters:
		var genre = $('#genre').val();
		var platform = $('#platform').val();
		var ordering = $('#order').val();
		var name = $('#name').val();
		 
		$.get('gamegenerator.php?genre=' + genre + '&platform=' + platform + '&name=' + name + '&order=' + ordering, function(data, status) {
			$('#game-content').html(data);
		});
		
		$('#reselect-games').show();
		$('#error-message').text('');
	});
	
	$('#new-search').click(function() {
		$('#error-message').text('');
		$('#game-content').hide();
		$('#reselect-games').hide();
		$('#game-form').show();
	});
});

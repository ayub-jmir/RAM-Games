$(document).ready(function() {
	
	// On submission of date form
	$('#submit-date').click(function() {
				
		var date = $('#date').val();
		var nights = $('#nights').val();
		var today = new Date();
		var dateDate = new Date(Date.parse(date));
		dateDate.setHours(24, 0, 0, 0); // There is a bug because of conversion of UTC to EST/EDT. This fixes it.
		today.setHours(0, 0, 0, 0);
		
		// Dates in the past are errors.
		// setHours ensures dates will be compared to the same time of day
		if (dateDate < today) {
			$('#error-message').text('Date must not be in the past!');
			return;
		}
		
		$('#date-form').hide();
		$('#room-content').show();
		
		// Ordering parameters:
		var beds = $('#beds').val();
		var ordering = $('#order').val();
		
		$.get('roomgenerator.php?date=' + date + '&nights=' + nights + '&beds=' + beds + '&order=' + ordering, function(data, status) {
			$('#room-content').html(data);
		});
		
		$('#reselect-rooms').show();
		$('#error-message').text('');
	});
	
	// Re-show date form if new dates chosen
	$('#new-date').click(function() {
		$('#error-message').text('');
		$('#room-content').hide();
		$('#reselect-rooms').hide();
		$('#date-form').show();
	});
});
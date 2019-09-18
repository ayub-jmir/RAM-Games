$(document).ready(function() {
	
	$('#submit').click(function() {
		
		var cardNumber = $('#credit-card').val();
		var cardSecurityCode = $('#credit-card-csc').val();
		var expMonth = Number($('#expiration-month').val());
		var expYear = $('#expiration-year').val();
		
		var today = new Date();
		
		var output = '';
		
		if (!cardNumber) {
			output += 'Please enter a credit card number.<br>';
		}
		
		if (!cardSecurityCode) {
			output += 'Please enter the card security code.<br>';
		}
		
		if (today.getFullYear() == expYear && today.getMonth() > expMonth - 1) { // -1 because of how JavaScript stores months
			output += 'That credit card is expired.<br>';
		}
		
		$('#error-message').html(output);
		
		// If an error occurred, return
		if (output.length > 0) {
			return false;
		}
		
		$('#payment-form').submit();
	});
	
});
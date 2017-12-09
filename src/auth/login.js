$(document).ready(function() {
	// login form helper
	$("#loginform").ajaxForm({
		success: function(response) {
			if (response.success) {
				// redirect user to where they want to go
				if (redirectUri)
					window.location.replace(redirectUri);
				else
					window.location.replace("/");
			} else {
				console.log(`Failed to login: ${JSON.stringify(response, null, 4)}.`);
				
				// shows the error text and sets the content to the server error
				var message = $("#errortext");
				message.removeClass("hidden");
				message.text(response.message);
			}
		}
	});
});

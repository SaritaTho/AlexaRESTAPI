$(document).ready(function() {
	const messageElement = $("#errortext");
	const spinnerElement = $("#submit-spinner");
	
	const spinClass = "fa fa-cog fa-spin";
	
	// shows an error to the user
	function showError(message) {
		if (message !== (null || undefined)) {
			messageElement.removeClass("hidden");
			messageElement.text(message);
		} else {
			messageElement.addClass("hidden");
		}
	}
	
	// set the spinner state on the login button
	function setSpinner(visible) {
		if (visible) {
			spinnerElement.addClass(spinClass);
		} else {
			spinnerElement.removeClass();
		}
	}
	
	// login form helper
	$("#loginform").ajaxForm({
		beforeSubmit: function() {
			setSpinner(true);
		},
		error: function(err) {
			setSpinner(false);
			
			console.log(`${err.status}: ${err.statusText}`);
			showError("A server error occured. Try again in a bit.");
		},
		success: function(response) {
			setSpinner(false);
			
			if (response.success) {
				// redirect the user
				window.location = redirectUri || "/";
			} else {
				console.log(`Failed to login:\n${JSON.stringify(response, null, 4)}`);
				showError(response.message);
			}
		}
	});
});

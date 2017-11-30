$(document).ready(function() {
	// login form helper
	$("#createform").ajaxForm({
		success: function(response) {
			if (response.success) {
				// login success
				window.location.replace("/dashboard");
			} else {
				// nope. login error.
				var message = $("#errortext");
				message.removeClass("hidden");
				message.text(response.message);
			}
		}
	});
});

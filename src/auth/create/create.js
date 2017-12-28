$(document).ready(function() {
	// login form helper
	$("#createform").ajaxForm({
		success: function(response) {
			if (response.success) {
				// login success
				let redirectTo = redirectUri || "/dashboard";
				console.log(`Redrecting: ${redirectTo}`);
				window.location = redirectTo;
			} else {
				// nope. login error.
				let message = $("#errortext");
				message.removeClass("hidden");
				message.text(response.message);
			}
		}
	});
});

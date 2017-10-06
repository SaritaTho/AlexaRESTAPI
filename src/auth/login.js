$(document).ready(function() {
	// login form helper
	$("#loginform").ajaxForm({
		success: function(response) {
			if (response.success) {
				if (redirectUri)
					window.location.replace(redirectUri);
				else
					window.location.replace("/");
			} else {
				var message = $("#errortext");
				message.removeClass("hidden");
				message.text(response.message);
			}
		}
	});
});

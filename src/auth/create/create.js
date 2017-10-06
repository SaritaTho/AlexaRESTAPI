$(document).ready(function() {
	// login form helper
	$("#createform").ajaxForm({
		success: function(response) {
			if (response.success) {
				window.location.replace("/dashboard");
			} else {
				var message = $("#errortext");
				message.removeClass("hidden");
				message.text(response.message);
			}
		}
	});
});

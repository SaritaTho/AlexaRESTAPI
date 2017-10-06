function logoutToken(hash) {
	let body = `action=logouttoken&hash=${hash}`;
	
	let xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function() {
		if (xhr.readyState != xhr.DONE)
			return;
		
		if (xhr.status != 200)
		{
			alert("Internal error removing token.");
			return;
		}
		
		console.log(xhr.responseText);
		let response = JSON.parse(xhr.responseText);
		
		if (!response.success)
		{
			alert(`Error removing token: ${response.message}`);
			return;
		}
		
		$(`#${hash}`).remove();	// remove hash quickly
	};
	
	xhr.open("POST", "/dashboard/account/actionhandler.php", true);
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=utf-8;");
	xhr.send(body);
}

function logoutAll() {
	alert("logging out all");
}

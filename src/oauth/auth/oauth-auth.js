function Submit(accepted) {
	window.location = `.?${$.param(authRequest, false)}&accepted=${accepted}&authkey=${authKey}`;
}

async function deleteClientInfo(email) {
	const ok = window.confirm(`Are you sure you want to delete ALL client info for ${email}? This cannot be undone.`);
	if(!ok) return;

	const res = await fetch(`/api/deleteClientInfo.php?email=${encodeURIComponent(email)}`);
	if(res.status != 200) {
		const message = `Failed to delete client info: ${res.statusText}`;
		location.replace(`/viewClientInfo.php?msg=${encodeURIComponent(message)}`);
		return;
	}
	
	const stat = await res.json();
	
	let message = "Failed to delete client info";
	if(stat.success) {
		message = "Successfully deleted client info";
	}

	location.replace(`/viewClientInfo.php?msg=${encodeURIComponent(message)}`);
}
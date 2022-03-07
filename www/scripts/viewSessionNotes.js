async function deleteSession(id) {
	const ok = confirm("Are you sure you want to delete this session's Notes? It cannot be undone.");
	if(!ok) return;

	const res = await fetch(`/api/deleteSessionNotes.php?id=${id}`);
	if(res.status != 200) {
		const message = `Failed to delete session: ${res.statusText}`;
		location.replace(`/viewSessionNotes.php?msg=${encodeURIComponent(message)}`);
		return;
	}

	const stat = await res.json();
	let message = "Failed to delete session";
	if(stat.success) {
		message = "Successfully deleted session";
	}

	location.replace(`/viewSessionNotes.php?msg=${encodeURIComponent(message)}`);
}
async function deleteGoal(id) {
	const ok = confirm("Are you sure you want to delete this goal? It cannot be undone.");
	if(!ok) return;

	const res = await fetch(`/api/deleteGoal.php?id=${id}`);
	if(res.status != 200) {
		const message = `Failed to delete goal: ${res.statusText}`;
		location.replace(`/viewGoals.php?msg=${encodeURIComponent(message)}`);
		return;
	}

	const stat = await res.json();
	let message = "Failed to delete goal";
	if(stat.success) {
		message = "Successfully deleted goal";
	}

	location.replace(`/viewGoals.php?msg=${encodeURIComponent(message)}`);
}
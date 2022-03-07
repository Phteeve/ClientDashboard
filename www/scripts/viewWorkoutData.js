async function deleteWorkoutData(id) {
	const ok = confirm("Are you sure you want to delete this Workout's data? It cannot be undone.");
	if(!ok) return;

	const res = await fetch(`/api/deleteWorkoutData.php?id=${id}`);
	if(res.status != 200) {
		const message = `Failed to delete Workout Data: ${res.statusText}`;
		location.replace(`/viewWorkoutData.php?msg=${encodeURIComponent(message)}`);
		return;
	}

	const stat = await res.json();
	let message = "Failed to delete Workout Data";
	if(stat.success) {
		message = "Successfully deleted Workout Data";
	}

	location.replace(`/viewWorkoutData.php?msg=${encodeURIComponent(message)}`);
}
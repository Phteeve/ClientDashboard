async function markQuestionAsRead(uid) {
	const res = await fetch(`/api/readQuestion.php?uid=${uid}`);
	if(res.status != 200) {
		const msg = `Error: ${res.status} (${res.statusText})`;
		location.replace(`/viewQuestions.php?msg=${encodeURIComponent(msg)}`);
		return;
	}

	const body = await res.json();
	if(!body.success) { 
		location.replace(`/viewQuestions.php?msg=${encodeURIComponent("Failed to mark question as read")}`);
		return;
	}

	location.reload();
}
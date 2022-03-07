const userSelect = document.getElementById("user-select");
if(userSelect) userSelect.onchange = () => {
    const value = userSelect.value;
    location.search = `email=${encodeURIComponent(value)}`;
};

renderChart();

async function renderChart() {
    // Retrieve email from GET params, and if it's undefined, set it to an empty string
    const email = getParams().email ?? "";

    const result = await fetch(`/api/getTimeQueryData.php?email=${encodeURIComponent(email)}`);
    const body = await result.json();
    const ctx = document.getElementById("WorkoutDataChart").getContext("2d");
    const myChart = new Chart(ctx, {
        type: 'line',
        data: body,
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    for (let i = 1; i < 12; i++){
        myChart.getDatasetMeta(i).hidden=true;
    }
    myChart.update();

}

function getParams() {
    let query = location.search;
    if(query.length == 0) return {};

    query = query.substring(1); // Remove leading '?'
    const parts = query.split("&");
    const res = {};
    for(const part of parts) {
        const pair = part.split("=");
        const key = decodeURIComponent(pair[0]);
        const value = decodeURIComponent(pair[1]);
        res[key] = value;
    }

    return res;
}
<?php
require_once("../lib/authentication.php");

session_start();
auth\check_perms("user");

// If we're an admin, show the user selector
$client_data = [];
if(auth\has_auth_level("admin")) {
	$dat_ini = parse_ini_file("dat.ini");
	$ini_file = realpath($dat_ini["working_dir"] . "/super_secret_stuff/info.ini");
	$ini_info = parse_ini_file($ini_file);

	//connect to server and select database
	$servername = $ini_info["servername"];
	$username = $ini_info["username"];
	$password = $ini_info["password"];
	$dbname = $ini_info["dbname"];

	$mysqli = mysqli_connect($servername, $username, $password, $dbname);
	$query = "
		SELECT email, CONCAT(firstName, ' ', lastName) AS name
		FROM ClientInfo
		WHERE active = 1
		ORDER BY name
	";

	$res = mysqli_query($mysqli, $query);
	while($row = mysqli_fetch_assoc($res)) {
		$client_data[] = $row;
	}

	mysqli_close($mysqli);
}

?>


<!DOCTYPE HTML>
<html>
	<head>
		<title>Progress Visualization</title>
		<?php readfile("../components/Head.html"); ?>
	</head>
	
	<body>
		<?php
			require_once("../components/Header.php");
			Header::render();
		?>
		<main>
			<h1>Progress Visualization</h1>

			<?php
				if(auth\has_auth_level("admin")) {
					$user_email = filter_input(INPUT_GET, "email", FILTER_SANITIZE_EMAIL) ?? $_SESSION["user_email"];

					echo("<label>");
					echo("Select user:&nbsp;");
					echo("<select id='user-select'>");
					
					foreach($client_data as $cd) {
						$email = $cd["email"];
						$name = $cd["name"];
						if($email == $user_email) echo("<option value='$email' selected>$name</option>");
						else echo("<option value='$email'>$name</option>");
					}

					echo("</select>");
					echo("</label><br />");
				}
			?>

			<canvas id="WorkoutDataChart"></canvas>
		</main>

		<?php readfile("../components/Footer.html"); ?>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js"></script>
		<script src ="/scripts/WorkoutChart.js"></script>
	</body>
</html>

<?php
require_once("../lib/authentication.php");

session_start();

//failed authentication for any other pages leads here
//failed authentication for this page kicks people out to index.php
if(!auth\has_auth_level("user")){
    header("Location: /index.php?msg=" . urlencode("Insufficient permissions"), true, 302);
}

$dat_ini = parse_ini_file("dat.ini");
$ini_file = realpath($dat_ini["working_dir"] . "/super_secret_stuff/info.ini");
$ini_info = parse_ini_file($ini_file);

// Connect to server and select database
$servername = $ini_info["servername"];
$username = $ini_info["username"];
$password = $ini_info["password"];
$dbname = $ini_info["dbname"];

// Create connection
$mysqli = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$mysqli) {
    die("[ERROR][MySQL] " . mysqli_connect_error());
}

// Get user info
$email = $_SESSION["user_email"];
$query = "SELECT * FROM ClientInfo WHERE email='$email'";
$res = mysqli_query($mysqli, $query);
if(!$res) exit("[ERROR][MySQL] " . mysqli_error($mysqli));

// Fetch
$user_info = mysqli_fetch_assoc($res);
mysqli_close($mysqli);

?>

<!DOCTYPE HTML>
<html>
	<head>
		<title>Dashboard</title>
		<?php readfile("../components/Head.html"); ?>
	</head>
	
	<body>
		<?php
			require_once("../components/Header.php");
			Header::render();
		?>

		<main>
            <h1>Dashboard</h1>
            <?php
            if(isset($_GET["msg"])) {
                    echo("<p style='color: red;'>" . htmlspecialchars(urldecode($_GET["msg"])) . "</p>");
                }
            ?>
            <p>
                Currently logged in as <?php echo($user_info["firstName"] . " ($email)"); ?>
            </p>
            <h3>Actions:</h3>
            <ul class="linklist">
                <li><a href="/newWorkoutData.php">New Workout Data</a></li>    
                <?php
                if(auth\get_auth_level() == "user"){
                echo '<li><a href="/askQuestions.php">Ask Questions</a></li>';
                }
                ?>
                <li><a href="/addGoals.php">Add Goals</a></li>
                <li><a href="/viewClientInfo.php">Client Info</a></li>
                <li><a href="/viewGoals.php">View Goals</a></li>
                <li><a href="/viewSessionNotes.php">View Session Notes</a></li>
                <li><a href="/viewWorkoutData.php">View Workout Data</a></li>
                <li><a href="/dataVisualization.php">Progess Visualization</a></li>
                <li><a href="/logout.php">Log Out</a></li>
            </ul>

            <?php
                if(auth\has_auth_level("admin")) {
                    echo('
                    <h3>Administrative Actions:</h3>
                    <ul class="linklist">
                        <li><a href="/addClientInfo.php">Add Client Info</a></li>
                        <li><a href="/newAccount.php">Create Account</a></li>
                        <li><a href="/sessionNotes.php">Session Notes</a></li>
                            <!-- <li><a href="/infoQueries.php">Info Queries</a></li> -->
                        <li><a href="/viewQuestions.php">Clients Questions</a></li>
                    </ul>
                    ');
                }
            ?>
		</main>

		<?php readfile("../components/Footer.html"); ?>
	</body>
</html>
<?php
require_once("../lib/authentication.php");

session_start();
auth\check_perms("user");

$dat_ini = parse_ini_file("dat.ini");
$ini_file = realpath($dat_ini["working_dir"] . "/super_secret_stuff/info.ini");
$ini_info = parse_ini_file($ini_file);

//connect to server and select database
$servername = $ini_info["servername"];
$username = $ini_info["username"];
$password = $ini_info["password"];
$dbname = $ini_info["dbname"];

$mysqli = mysqli_connect($servername, $username, $password, $dbname);


$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$goals = filter_input(INPUT_POST, 'goals', FILTER_SANITIZE_SPECIAL_CHARS);

if(auth\get_auth_level() == "user"){
    $email = $_SESSION["user_email"];
}


if ($email != "" && $goals != ""){
    $addGoal = "INSERT INTO UserGoals (email, goals) VALUES ('$email', '$goals')";  
    mysqli_query($mysqli, $addGoal);
    mysqli_close($mysqli);
    header("Location: /addGoals.php?msg=" . urlencode("Goal added"), true, 302);
    exit;
}

//populate select dropdown
$sql = "SELECT firstName, lastName, email FROM ClientInfo WHERE active=1 ORDER BY firstname";

$result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
if (mysqli_num_rows($result) > 0) {
    $info = "<label for='email'>Select a User:&nbsp;</label>";
    $info .= "<select name='email' id='email'>";
    $counter = 0;
    while ($row = mysqli_fetch_array($result)) {
        $info .= "<option value= '$row[2]'>$row[0] $row[1]</option>";
    }  
    $info .= "</select><br>";
}
mysqli_close($mysqli);

?>

<!DOCTYPE HTML>
<html>
	<head>
		<title>Add Goals</title>
		<?php readfile("../components/Head.html"); ?>
	</head>
	
	<body>
		<?php
			require_once("../components/Header.php");
			session_start();
			Header::render();
		?>

		<main>
                <?php
                    if(isset($_GET["msg"])) {
                        echo("<p style='color: red;'>" . htmlspecialchars(urldecode($_GET["msg"])) . "</p>");
                    }
                ?>
            <h1>Add Goal</h1>
            <form action="addGoals.php" method="POST">
                <?php
                    if(auth\has_auth_level("admin")) {
                        echo($info);
                    }
                ?>

                <label for="goals">Goal</label> <br />
                <textarea id="goals" name="goals" rows="6" cols="50"></textarea><br>
                <button type="submit">Submit</button>

            </form>
		</main>
		<?php readfile("../components/Footer.html"); ?>
	</body>
</html>

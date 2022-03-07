<?php
require_once("../../lib/authentication.php");

session_start();
auth\check_perms("user");

$dat_ini = parse_ini_file("../dat.ini");
$ini_file = realpath($dat_ini["working_dir"] . "/super_secret_stuff/info.ini");
$ini_info = parse_ini_file($ini_file);

//connect to server and select database
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

header("Content-Type: application/json");

// Get data
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if(!$id) {
	http_response_code(400);
	exit(json_encode([ "error" => "Bad request" ]));
}

$email = $_SESSION["user_email"];
$check = "AND email = '$email'";
if(auth\has_auth_level("admin")) $check = ""; // If we're an admin, we can delete any Workout information

$query = "
	DELETE FROM UserData
	WHERE id = $id $check
";

$res = mysqli_query($mysqli, $query);
$json = json_encode([
	"success" => $res && mysqli_affected_rows($mysqli) // res has some value and we affected more than zero rows
]);

echo($json);
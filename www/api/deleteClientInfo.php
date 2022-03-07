<?php
require_once("../../lib/authentication.php");

session_start();
auth\check_perms("admin");

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

// Generate query
$email = filter_input(INPUT_GET, "email", FILTER_SANITIZE_EMAIL);
$query = "
	DELETE FROM ClientInfo
	WHERE email = '$email'
";

// Execute query
$res = mysqli_query($mysqli, $query);

// Create & send response
$json = json_encode([
	"success" => (bool)$res
]);

header("Content-Type: application/json");
echo($json);
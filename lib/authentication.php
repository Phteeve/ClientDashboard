<?php namespace auth;

// This is basically to make auth levels like an enum (safer, prevents typos - this
// is important in a security context), and allows for easy checking of perms.

// All of the different auth levels ordered from lowest to highest
const AUTH_LEVELS = [
	"none",
	"user",
	"admin"
];

// Gets the current user's auth level
function get_auth_level() : string {
	if(isset($_SESSION["AuthLevel"])) return $_SESSION["AuthLevel"];
	else return AUTH_LEVELS[0];
}

// Sets the user's current auth level
function set_auth_level(string $value) {
	if(!in_array($value, AUTH_LEVELS)) throw new \Exception("Invalid auth level");
	$_SESSION["AuthLevel"] = $value;
}

// Checks if the user's current auth level is higher or equal to the one specified
function has_auth_level(string $level) : bool {
	if(!in_array($level, AUTH_LEVELS)) throw new \Exception("Invalid auth level");
	if(!isset($_SESSION["AuthLevel"])) return false;

	$checked = array_search($level, AUTH_LEVELS);
	$current = array_search($_SESSION["AuthLevel"], AUTH_LEVELS);
	return $current >= $checked;
}

// Checks the user's perms and redirects them if they're not good enough
function check_perms(string $auth_level) {
	if(has_auth_level($auth_level)) return;

	header("Location: /dashboard.php?msg=" . urlencode("Insufficient permissions"), true, 302);
	exit();
}
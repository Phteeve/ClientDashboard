<?php

require_once("../lib/authentication.php");

class Header {
	public static function render() {
		echo("<header><h1>Client Dashboard</h1><nav><a href='/index.php'>Home</a>");
		if(auth\has_auth_level("user")) {
			// TODO: Account page
			echo("<a href='/dashboard.php'>Dashboard</a>");
		} else {
			echo("<a href='/userLogin.php'>User Login</a>");
		}

		echo("</nav></header>");
	}
}
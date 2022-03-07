<!DOCTYPE html>
<html>
	<head>
		<title>Contact Us</title>
		<?php readfile("../components/Head.html"); ?>
	</head>

	<body>
		<?php
			require_once("../components/Header.php");
			session_start();
			Header::render();
		?>

		<main>
			<h4>Contact Nathan: nathan@hoopstrengthkelowna.com </h4> <br /> <br />
			<h4>Contact the Developer: stephen.herbert@telus.net </h4> <br /> <br />
			<h5> Note we are looking for a front end developer to add some style to this project, contact Stephen if interested <h5>
		</main>

		<?php readfile("../components/Footer.html"); ?>
	</body>
</html>
<?php

// TODO: Normal user functionality
require_once("../lib/authentication.php");
require_once("../components/Header.php");

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

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    // Make sure the user has the right email
    if(!auth\has_auth_level("admin") && $email != $_SESSION["user_email"]) {
        $email = $_SESSION["user_email"];
    }

    $height = filter_input(INPUT_POST, 'height', FILTER_SANITIZE_SPECIAL_CHARS);
    $weight = filter_input(INPUT_POST, 'weight', FILTER_SANITIZE_SPECIAL_CHARS);
    $bodyFatPercent = filter_input(INPUT_POST, 'bodyFatPercent', FILTER_SANITIZE_SPECIAL_CHARS);
    $ankleDorsiFlexion = filter_input(INPUT_POST, 'ankleDorsiFlexion', FILTER_SANITIZE_SPECIAL_CHARS);
    $activeStraightLegRaise = filter_input(INPUT_POST, 'activeStraightLegRaise', FILTER_SANITIZE_SPECIAL_CHARS);
    $internalHipRotation = filter_input(INPUT_POST, 'internalHipRotation', FILTER_SANITIZE_SPECIAL_CHARS);
    $supineOverheadReach = filter_input(INPUT_POST, 'supineOverheadReach', FILTER_SANITIZE_SPECIAL_CHARS);
    $squatJumpHeight = filter_input(INPUT_POST, 'squatJumpHeight', FILTER_SANITIZE_SPECIAL_CHARS);
    $reactiveStrengthIndex = filter_input(INPUT_POST, 'reactiveStrengthIndex', FILTER_SANITIZE_SPECIAL_CHARS);
    $eccentricUtilizationRatio = filter_input(INPUT_POST, 'eccentricUtilizationRatio', FILTER_SANITIZE_SPECIAL_CHARS);
    $isometricMidThighPull = filter_input(INPUT_POST, 'isometricMidThighPull', FILTER_SANITIZE_SPECIAL_CHARS);
    $contactAndFlightTimeAsymmetry = filter_input(INPUT_POST, 'contactAndFlightTimeAsymmetry', FILTER_SANITIZE_SPECIAL_CHARS);

    if ($email != ""){
        $sql = "INSERT INTO UserData (email, height, weight, bodyFatPercent, ankleDorsiFlexion, activeStraightLegRaise, internalHipRotation, supineOverheadReach, squatJumpHeight, reactiveStrengthIndex, eccentricUtilizationRatio, isometricMidThighPull, contactAndFlightTimeAsymmetry) VALUES ('$email', '$height', '$weight', '$bodyFatPercent', '$ankleDorsiFlexion', '$activeStraightLegRaise', '$internalHipRotation', '$supineOverheadReach', '$squatJumpHeight', '$reactiveStrengthIndex', '$eccentricUtilizationRatio', '$isometricMidThighPull', '$contactAndFlightTimeAsymmetry')";
        mysqli_query($mysqli, $sql);
        mysqli_close($mysqli);
        header("Location: /dashboard.php?msg=" . urlencode("Added new workout data"), true, 302);
        exit;
    }
}

if(auth\has_auth_level("admin")) {
    $sql = "SELECT firstName, lastName, email FROM ClientInfo ORDER BY firstName";
    $result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    if (mysqli_num_rows($result) > 0) {
        $info = "<label for='email'>Select a User:&nbsp;</label>";
        $info .= "<select name='email' id='email'>";
        $counter = 0;
        while ($row = mysqli_fetch_array($result)) {
            $info .= "<option value= '$row[2]'>$row[0] $row[1]</option>";
        }  
        $info .= "</select><br><br>";
    }
}

mysqli_close($mysqli);

?>


<html>
    <head>
        <title>New Workout Data</title>
        <?php readfile("../components/Head.html"); ?>
    </head>
    <body>
        <?php Header::render(); ?>

        <main>
                <?php
                    if(isset($_GET["msg"])) {
                        echo("<p style='color: red;'>" . htmlspecialchars(urldecode($_GET["msg"])) . "</p>");
                    }
                ?>

            <form name ="WDForm" action="newWorkoutData.php" method="POST">
                <h3>New Workout Data:</h3>
                    <?php echo $info ?>
                    <label for="Height">Height:</label>
                        <input type="text" name="height" id="height" /><br />
                    <label for="Weight">Weight:</label>
                        <input type="text" name="weight" id="weight" /> <br />
                    <label for="bodyFatPercent">Body Fat Percentage:</label>
                        <input type="text" name="bodyFatPercent" id="bodyFatPercent" /> <br />
                    <label for="ankleDorsiFlexion">Ankle Dorsi Flexion:</label>
                        <input type="text" name="ankleDorsiFlexion" id="ankleDorsiFlexion" /> <br />
                    <label for="activeStraightLegRaise">Active Straight Leg Raise:</label>
                        <input type="text" name="activeStraightLegRaise" id="activeStraightLegRaise" /> <br />
                    <label for="internalHipRotation">Internal Hip Rotation:</label>
                        <input type="text" name="internalHipRotation" id="internalHipRotation" /> <br />
                    <label for="supineOverheadReach">Supine Overhead Reach:</label>
                        <input type="text" name="supineOverheadReach" id="supineOverheadReach" /> <br />
                    <label for="squatJumpHeight">Squat Jump Height:</label>
                        <input type="text" name="squatJumpHeight" id="squatJumpHeight" /> <br />
                    <label for="reactiveStrengthIndex">Reactive Strength Index:</label>
                        <input type="text" name="reactiveStrengthIndex" id="reactiveStrengthIndex"/> <br />
                    <label for="eccentricUtilizationRatio">Eccentric utilization Ratio:</label>
                        <input type="text" name="eccentricUtilizationRatio" id="eccentricUtilizationRatio" /> <br />
                    <label for="isometricMidThighPull">Isometric Mid Thigh Pull:</label>
                        <input type="text" name="isometricMidThighPull" id="isometricMidThighPull" /> <br />
                    <label for="contactAndFlightTimeAsymmetry">Contact And Flight Time Asymmetry:</label>
                        <input type="text" name="contactAndFlightTimeAsymmetry" id="contactAndFlightTimeAsymmetry" /> <br />
                    <input type="submit" name="submit" value="Submit"/>
            </form>
        </main>

        <?php readfile("../components/Footer.html"); ?>
    </body>
</html>
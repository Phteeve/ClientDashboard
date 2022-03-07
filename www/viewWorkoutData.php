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

// Create connection
$mysqli = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$mysqli) {
  die("Connection failed: " . mysqli_connect_error());
}

$user_data_list = [];
if($_SERVER["REQUEST_METHOD"] == "POST" && auth\has_auth_level("admin")) {
    // Search user
    $name = filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
    
    // Split name by whitespace to extract search terms
    $name_list = preg_split("/\s/", $name);

    /**
     * Example: ["kurtis", "knodel"] becomes
     * "(firstName LIKE '%kurtis%' OR lastName LIKE '%kurtis%') AND (firstName LIKE '%knodel%' OR lastName LIKE '%knodel%)'
     */
    $name_query = implode(" AND ", array_map(
        function($n) {
            return "(firstName LIKE '%$n%' OR lastName LIKE '%$n%')";
        },
        $name_list
    ));

    // Create full query
    $query = "
        SELECT ud.*, CONCAT(ci.firstName, ' ', ci.lastName) AS name
        FROM UserData ud
        INNER JOIN ClientInfo ci
        ON ud.email = ci.email
        WHERE $name_query
        ORDER BY ci.firstName
    ";

    // Execute query
    $res = mysqli_query($mysqli, $query);
    if(!$res) exit("[ERROR][MySQL] " . mysqli_error($mysqli));

    // Get data
    while($row = mysqli_fetch_assoc($res)) {
        $user_data_list[] = $row;
    }
} else {
    // Get user info
    $email = $_SESSION["user_email"];
    $query = "
        SELECT CONCAT(ci.firstName, ' ', ci.lastName) AS name, ud.*
        FROM UserData ud
        LEFT JOIN ClientInfo ci
        ON ud.email = ci.email
        WHERE ud.email = '$email'
        ORDER BY ci.firstName
    ";

    // Execute query
    $res = mysqli_query($mysqli, $query);
    if(!$res) exit("[ERROR][MySQL] " . mysqli_error($mysqli));

    // Get data
    $res = mysqli_fetch_assoc($res);
    if($res) $user_data_list[] = $res;
}

mysqli_close($mysqli);

?>

<!DOCTYPE HTML>
<html>
	<head>
		<title>Workout Data</title>
		<?php readfile("../components/Head.html"); ?>
	</head>
	
	<body>
		<?php
			require_once("../components/Header.php");
			Header::render();
		?>

		<main>
			<h1>Workout Data</h1>
            <?php
				if(isset($_GET["msg"])) {
					echo("<p style='color: red;'>" . htmlspecialchars(urldecode($_GET["msg"])) . "</p>");
				}

                if(auth\has_auth_level("admin")) {
                    require_once("../components/UserSearch.php");
                    UserSearch::render("/viewWorkoutData.php");
                }
            ?>

            <table>
                <tr class="head">
                    <?php
                        $col_names = array("Name", "Height", "Weight", "BF%", "Ankle Dorsi Flexion", "Active Straight Leg Raise", "Internal Hip Rotation", "Supine Overhead Reach", "Squat Jump Height", "Reactive Strength Index", "Eccentric Utilization Ratio", "Isometric Mid Thigh Pull", "Contact And Flight Time Asymmetry");
                        foreach($col_names as $c) {
                            echo("<td>$c</td>");
                        }

                        echo("<td></td>"); // Add another td so the CSS looks nice
                    ?>
                </tr>
                <?php
                    if(!$user_data_list) {
                        echo("<p style='color: red;'>No results</p>");
                    }

                    foreach($user_data_list as $data) {
                        echo("<tr>");

                        $name = $data["name"];
                        echo("<td>$name</td>");

                        $height = $data["height"];
                        echo("<td>$height</td>");

                        $weight = $data["weight"];
                        echo("<td>$weight</td>");

                        $bfp = $data["bodyFatPercent"];
                        echo("<td>$bfp</td>");

                        $adf = $data["ankleDorsiFlexion"];
                        echo("<td>$adf</td>");

                        $aslr = $data["activeStraightLegRaise"];
                        echo("<td>$aslr</td>");

                        $ihr = $data["internalHipRotation"];
                        echo("<td>$ihr</td>");

                        $sor = $data["supineOverheadReach"];
                        echo("<td>$sor</td>");

                        $sjh = $data["squatJumpHeight"];
                        echo("<td>$sjh</td>");

                        $rsi = $data["reactiveStrengthIndex"];
                        echo("<td>$rsi</td>");

                        $eur = $data["eccentricUtilizationRatio"];
                        echo("<td>$eur</td>");

                        $imtp = $data["isometricMidThighPull"];
                        echo("<td>$imtp</td>");

                        $cfa = $data["contactAndFlightTimeAsymmetry"];
                        echo("<td>$cfa</td>");

                        $uid = $data["id"];
                        echo("<td><button class='danger' onclick='javascript:deleteWorkoutData($uid)'>Delete Workout Data</button></td>");

                        echo("</tr>");
                    }
                ?>
            </table>
		</main>
		<?php readfile("../components/Footer.html"); ?>
        <script src="/scripts/viewWorkoutData.js"></script>
	</body>
</html>

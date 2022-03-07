<?php
require_once("../lib/authentication.php");
require_once("../components/Header.php");
require_once("../components/UserSearch.php");

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

$col = array("Name", "Session Notes");
$numcol = count($col);

// Create connection
$mysqli = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if (!$mysqli) {
  die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT CONCAT(firstName, ' ', lastName), notes, SessionNotes.id FROM ClientInfo, SessionNotes WHERE SessionNotes.email = ClientInfo.email";

if(auth\get_auth_level() == "user"){
    $email = $_SESSION["user_email"];
    $sql = "SELECT CONCAT(firstName, ' ', lastName), notes, SessionNotes.id FROM ClientInfo, SessionNotes WHERE SessionNotes.email = ClientInfo.email AND SessionNotes.email = '" . $email . "'";
} elseif(auth\has_auth_level("admin") && $_SERVER["REQUEST_METHOD"] == "POST") {
    $name = filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
    $where = implode(" AND ", array_map(
        function($n) {
            return "(ci.firstName LIKE '%$n%' OR ci.lastName LIKE '%$n%')";
        },
        preg_split("/\s/", $name)
    ));

    $sql = "
        SELECT CONCAT(ci.firstName, ' ', ci.lastName), sn.notes, sn.id
        FROM SessionNotes sn
        LEFT JOIN ClientInfo ci
        ON sn.email = ci.email
        WHERE $where
    ";
}

$result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
if (mysqli_num_rows($result) > 0) {
	$info = "<table><tr class='head'>";
        for ($i = 0; $i < count($col); $i++) {
            $info .= "<td>" . $col[$i] . "</td>";
        }

        $info .= "<td></td>";
	$info .= "</tr>";
    while ($row = mysqli_fetch_array($result)) {
        
        $info .= "<tr>";
        for ($i = 0; $i < count($col); $i++) {
            $info .= "<td>" . $row[$i] . "</td>";
        }
        $info .= "<td><button class='danger' onclick='javascript:deleteSession($row[2])'>Delete Session Notes</button></td>";
        $info .=  "</tr>";
    }  
	$info .= "</table>"; 
} else {
    $info = "<p style='color: red;'>No results</p>";
}
mysqli_close($mysqli);
?>
<html>
    <head>
        <title>Database List</title>
        <?php readfile("../components/Head.html"); ?>
    </head>
    <body>
        <?php Header::render(); ?>

        <main>
            <h1>Session Notes</h1>
            <?php
				if(isset($_GET["msg"])) {
					echo("<p style='color: red;'>" . htmlspecialchars(urldecode($_GET["msg"])) . "</p>");
				}

                if(auth\has_auth_level("admin")) {
                    UserSearch::render("/viewSessionNotes.php");
                }
			?>

            <?php echo $info ?> </br>
        </main>

        <?php readfile("../components/Footer.html"); ?>
        <script src="/scripts/viewGoals.js"></script>
    </body>
</html>
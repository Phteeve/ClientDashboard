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

$user_goal_list = [];
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
        SELECT CONCAT(ci.firstName, ' ', ci.lastName) AS name, ci.email, ug.goals
        FROM ClientInfo ci
        INNER JOIN UserGoals ug
        ON ci.email = ug.email
        WHERE $name_query
    ";

    // Execute query
    $res = mysqli_query($mysqli, $query);
    if(!$res) exit("[ERROR][MySQL] " . mysqli_error($mysqli));

    // Get data
    while($row = mysqli_fetch_assoc($res)) {
        $email = $row["email"];
        $goal = $row["goals"];
        $id = $row["id"];
        if(!isset($user_goal_list[$email])) {
            $name = $row["name"];
            $user_goal_list[$email] = [
                "name" => $name,
                "goals" => [],
            ];
        }

        $user_goal_list[$email]["goals"][] = [
            "goal" => $goal,
            "id" => $id
        ];
    }
} else {
    // Get user info
    $email = $_SESSION["user_email"];
    $query = "
        SELECT ug.*, CONCAT(ci.firstName, ' ', ci.lastName) AS name
        FROM UserGoals ug
        LEFT JOIN ClientInfo ci
        ON ug.email = ci.email
        WHERE ug.email = '$email'
    ";

    // Execute query
    $res = mysqli_query($mysqli, $query);
    if(!$res) exit("[ERROR][MySQL] " . mysqli_error($mysqli));

    // Get data
    while($row = mysqli_fetch_assoc($res)) {
        $email = $row["email"];
        $goal = $row["goals"];
        $id = $row["id"];
        if(!isset($user_goal_list[$email])) {
            $name = $row["name"];
            $user_goal_list[$email] = [
                "name" => $name,
                "goals" => [],
            ];
        }

        $user_goal_list[$email]["goals"][] = [
            "goal" => $goal,
            "id" => $id
        ];
    }
}

mysqli_close($mysqli);

?>

<!DOCTYPE HTML>
<html>
	<head>
		<title>Goals</title>
		<?php readfile("../components/Head.html"); ?>
	</head>
	
	<body>
		<?php
			require_once("../components/Header.php");
			Header::render();
		?>

		<main>
			<h1>Goals</h1>
            <?php
				if(isset($_GET["msg"])) {
					echo("<p style='color: red;'>" . htmlspecialchars(urldecode($_GET["msg"])) . "</p>");
				}
			?>
            <?php
                if(auth\has_auth_level("admin")) {
                    require_once("../components/UserSearch.php");
                    UserSearch::render("/viewGoals.php");
                }
            ?>

            <table>
                <tr class="head">
                    <td>Name</td>
                    <td>Email</td>
                    <td>Goals</td>
                </tr>
                <?php
                    if(!$user_goal_list) {
                        echo("<p style='color: red;'>No results</p>");
                    }

                    foreach($user_goal_list as $email => $info) {
                        echo("<tr>");

                        $name = $info["name"];
                        echo("<td>$name</td><td><a href='mailto:$email'>$email</a></td>");

                        echo("<td>");
                        $goals = $info["goals"];
                        foreach($goals as $g) {
                            $goal = $g["goal"];
                            $id = $g["id"];
                            
                            echo("<div class='spaced'>");
                            echo("<p>$goal</p>");
                            echo("<button class='danger' onclick='javascript:deleteGoal($id)'>Delete Goal</button>");
                            echo("</div>");
                        }
                        echo("</td>");
                        
                        echo("</tr>");
                    }
                ?>
            </table>
		</main>
		<?php readfile("../components/Footer.html"); ?>
        <script src="/scripts/viewGoals.js"></script>
	</body>
</html>

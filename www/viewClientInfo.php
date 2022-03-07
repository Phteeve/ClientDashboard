<?php
require_once("../lib/authentication.php");
require_once("../lib/formatter.php");

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

$client_info_list = [];
if($_SERVER["REQUEST_METHOD"] == "POST" && auth\has_auth_level("admin")) {
    // Search user
    $activity_level = filter_input(INPUT_POST, "activity_level", FILTER_SANITIZE_SPECIAL_CHARS);
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

    // Convert activity level to query string
    $active_query = "";
    if($activity_level == "active") {
        $active_query = "AND active = 1";
    } elseif($activity_level == "nactive") {
        $active_query = "AND active = 0";
    }

    // Create full query
    $query = "
        SELECT *
        FROM ClientInfo
        WHERE $name_query $active_query
    ";

    // Execute query
    $res = mysqli_query($mysqli, $query);
    if(!$res) exit("[ERROR][MySQL] " . mysqli_error($mysqli));

    // Get data
    while($row = mysqli_fetch_assoc($res)) {
        $client_info_list[] = $row;
    }
} else {
    // Get user info
    $email = $_SESSION["user_email"];
    $query = "
        SELECT *
        FROM ClientInfo
        WHERE email = '$email'
    ";

    // Execute query
    $res = mysqli_query($mysqli, $query);
    if(!$res) exit("[ERROR][MySQL] " . mysqli_error($mysqli));

    // Get data
    $res = mysqli_fetch_assoc($res);
    if($res) $client_info_list[] = $res;
}

mysqli_close($mysqli);

?>

<!DOCTYPE HTML>
<html>
	<head>
		<title>Client Info</title>
		<?php readfile("../components/Head.html"); ?>
	</head>
	
	<body>
		<?php
			require_once("../components/Header.php");
			Header::render();
		?>

		<main>
            <h1>Client Info</h1>           
            <?php
                if(isset($_GET["msg"])) {
                    echo("<p style='color: red;'>" . htmlspecialchars(urldecode($_GET["msg"])) . "</p>");
                }
            ?>

            <?php
                if(auth\has_auth_level("admin")) {
                    echo('
                        <form action="/viewClientInfo.php" method="POST">
                            <div style="margin: 1em 0;">
                                Client Activity:<br />
                                <input type="radio" id="rad-all" name="activity_level" value="all" checked />
                                <label for="rad-all">Any activity</label>

                                <input type="radio" id="rad-active" name="activity_level" value="active" />
                                <label for="rad-active">Active</label>

                                <input type="radio" id="rad-nactive" name="activity_level" value="nactive" />
                                <label for="rad-nactive">Not active</label>
                            </div>
                            
                            <label for="name">Client Name:</label>
                            <br />
                            <input type="search" name="name" />
                            <br />
                            <button type="submit">Search</button>
                        </form>
                    ');
                }
            ?>

            <table>
                <tbody>
                    <tr class="head">
                        <td>First Name</td>
                        <td>Last Name</td>
                        <td>Email</td>
                        <td>Address</td>
                        <td>Phone Number</td>
                        <td>Client Type</td>
                        <td>Active?</td>
                        <?php if(auth\has_auth_level("admin")) echo("<td></td>"); // Echo an extra TD if we're an admin to account for delete button ?>
                    </tr>                
                    <?php
                        if(!$client_info_list) {
                            echo("<p style='color: red;'>No results</p>");
                        }

                        foreach($client_info_list as $client) {
                            $first_name = $client["firstName"];
                            $last_name = $client["lastName"];
                            $email = $client["email"];
                            $address = $client["address"];
                            $phone_number = $client["phoneNumber"];
                            $type = ucwords($client["clientType"]);
                            $active = $client["active"] ? "Yes" : "No";
                            
                            $delete = null;
                            if(auth\has_auth_level("admin")) {
                                $delete = "
                                    <td><button class='danger' onclick='javascript:deleteClientInfo(\"$email\")'>Delete Client</button></td>
                                ";
                            }
                            
                            echo("
                                <tr>
                                    <td>$first_name</td>
                                    <td>$last_name</td>
                                    <td><a href='mailto:$email'>$email</a></td>
                                    <td>$address</td>
                                    <td><a href='tel:$phone_number'>" . formatter\format_phone($phone_number) . "</a></td>
                                    <td>$type</td>
                                    <td>$active</td>
                                    $delete
                                </tr>
                            ");
                        }
                    ?>
                </tbody>
            </table>
		</main>
		<?php readfile("../components/Footer.html"); ?>
        <script src="/scripts/viewClientInfo.js"></script>
	</body>
</html>

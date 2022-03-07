<?php

require_once("../lib/authentication.php");
require_once("../components/Header.php");

session_start();

// Nice and clean
auth\check_perms("admin");

$dat_ini = parse_ini_file("dat.ini");
$ini_file = realpath($dat_ini["working_dir"] . "/super_secret_stuff/info.ini");
$ini_info = parse_ini_file($ini_file);

//connect to server and select database
$servername = $ini_info["servername"];
$username = $ini_info["username"];
$password = $ini_info["password"];
$dbname = $ini_info["dbname"];

$mysqli = mysqli_connect($servername, $username, $password, $dbname);

$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$firstName = filter_input(INPUT_POST, 'fname', FILTER_SANITIZE_SPECIAL_CHARS);
$lastName = filter_input(INPUT_POST, 'lname', FILTER_SANITIZE_SPECIAL_CHARS);
$address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS);
$phoneNumber = filter_input(INPUT_POST, 'phoneNumber', FILTER_SANITIZE_SPECIAL_CHARS);
$birthday = filter_input(INPUT_POST, 'birthday', FILTER_SANITIZE_SPECIAL_CHARS);
$clientType = filter_input(INPUT_POST, 'clientType', FILTER_SANITIZE_SPECIAL_CHARS);
$goals = filter_input(INPUT_POST, 'goals', FILTER_SANITIZE_SPECIAL_CHARS);
$active = filter_input(INPUT_POST, 'active', FILTER_VALIDATE_BOOLEAN);

// Format phone number so that it will fit within 15 chars (hopefully)
$phoneNumber = preg_replace("/\D/", "", $phoneNumber);

if ($email != ""){
//run a query to check for an email already existing in the database
$sql = "SELECT * FROM ClientInfo WHERE email = '" . $email . "'";
$result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));

    if (mysqli_num_rows($result) > 0) {
        // If we're updating client info, only update stuff that was sent by the frontend
        // i.e. don't set unspecified values to blank/null
        $old_data = mysqli_fetch_assoc($result);
        if(!$email) $email = $old_data["email"];
        if(!$firstName) $firstName = $old_data["firstName"];
        if(!$lastName) $lastName = $old_data["lastName"];
        if(!$address) $address = $old_data["address"];
        if(!$birthday) $birthday = $old_data["birthday"];
        if(!$phoneNumber) $phoneNumber = $old_data["phoneNumber"];
        if(!$clientType) $clientType = $old_data["clientType"];

        $update = "UPDATE ClientInfo SET firstname='$firstName', lastname='$lastName', address='$address', phoneNumber='$phoneNumber', birthday='$birthday', clientType='$clientType', active='$active' WHERE email = '$email'";
        mysqli_query($mysqli, $update);
        if ($goals != "") {
            $addGoal = "INSERT INTO UserGoals (email, goals) VALUES ('$email', '$goals')";
            mysqli_query($mysqli, $addGoal);
        }
        mysqli_close($mysqli);
        header("Location: /addClientInfo.php?msg=" . urlencode("Client info updated"), true, 302);
        exit;

    } else {
        
        $insert = "INSERT INTO ClientInfo VALUES ('$email', '$firstName', '$lastName', '$address', '$phoneNumber', '$birthday', '$clientType', '$active')";
        mysqli_query($mysqli, $insert);
        if ($goals != "") {
            $addGoal = "INSERT INTO UserGoals (email, goals) VALUES ('$email', '$goals')";
            mysqli_query($mysqli, $addGoal);
        }
        mysqli_close($mysqli);
        header("Location: /addClientInfo.php?msg=" . urlencode("Added new client"), true, 302);
        exit;
    }
	mysqli_close($mysqli);
}

$sql = "SELECT email FROM AuthUsers ORDER BY email";

$result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
if (mysqli_num_rows($result) > 0) {
    $info = "<label for='email'>Select a User:&nbsp;</label>";
    $info .= "<select name='email' id='email'>";
    $counter = 0;
    while ($row = mysqli_fetch_array($result)) {
    
        $info .= "<option value= '$row[0]'>$row[0]</option>";
    }  
    $info .= "</select><br />";
}
mysqli_close($mysqli);

?>

<!-- add new data entry -->
<html>
    <head>
        <title>Client Information</title>
        <?php readfile("../components/Head.html"); ?>
    </head>
    <body>
        <?php Header::render(); ?>
        <main>
            <?php
                if(isset($_GET["msg"])) {
                    $msg = htmlspecialchars(urldecode($_GET["msg"]));
                    echo("<p style='color: red;'>$msg<p>");
                }
            ?>
            <form name ="CIForm" action="addClientInfo.php" method="POST">
                <h3>New User Information:</h3>
                <?php echo $info ?>
               
                <label for="fname">First Name:</label>
                <br />
                <input type="text" name="fname" id="fname" />
                
                <br />

                <label for="lname">Last Name:</label>
                <br />
                <input type="text" name="lname" id="lname" />

                <br />

                <label for="address">Address:</label>
                <br />
                <input type="text" name="address" id="address" />

                <br />

                <label for="phoneNumber">Phone Number:</label>
                <br />
                <input type="text" name="phoneNumber" id="phoneNumber" />

                <br />

                <label for="birthday">Birthday Date:</label>
                <br />
                <input type="date" name="birthday" id="birthday" />

                <br />

                <label for="clientType">Client Type:&nbsp;</label>
                <select name="clientType" id="clientType" >
                    <option value="group">Group</option>
                    <option value="semiPrivate">Semi Private</option>
                    <option value="online">Online</option>
                </select>

                <br />

                <label for="goals">Goals:</label>
                <br />
                <textarea name="goals" rows="6" cols="50" id="goals" ></textarea>

                <br />

                <label for="active">Active:</label>
                <input type="checkbox" name="active" id="active" checked />

                <br />

                <button type="submit">Submit</button>
            </form>
        </main>

        <?php readfile("../components/Footer.html"); ?>
    </body>
</html>
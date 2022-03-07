<?php
require_once("../lib/authentication.php");
require_once("../components/Header.php");
session_start();

if($_SERVER["REQUEST_METHOD"] == "POST") {
    // This is a hack... I'm sorry...
    $dat_ini = parse_ini_file("dat.ini");
    $ini_file = realpath($dat_ini["working_dir"] . "/super_secret_stuff/info.ini");
    $ini_info = parse_ini_file($ini_file, true);
    //if the parse_ini_file process sections = true, you MUST treat the ini key call like a 2D array ([section][key])
    //otherwise if left blank (default = false) you MUST treat the ini key call like a 1D array ([key])
    
    //connect to server and select database
    $servername = $ini_info["database"]["servername"];
    $username = $ini_info["database"]["username"];
    $password = $ini_info["database"]["password"];
    $dbname = $ini_info["database"]["dbname"];

    $mysqli = mysqli_connect($servername, $username, $password, $dbname);

    // Select users by email. This will result in zero or 1 row(s)
    // since email must be unique.
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    //fixed bug where case specific letters being the wrong case caused weird stuff to happen
    $email = strtolower($email);
    $sql = "SELECT * FROM AuthUsers WHERE email = '$email'";
    $result = mysqli_query($mysqli, $sql) or exit(mysqli_error($mysqli));
    mysqli_close($mysqli);

    // If there were no matches, the user doesn't exist. Redirect
    // with a message.
    if($result->num_rows == 0) {
        header("Location: /userLogin.php?msg=" . urlencode("User does not exist"), true, 302);
        exit();
    }

    // Grab the result data and the password the user input
    $result = mysqli_fetch_assoc($result);
    $password = $_POST["password"];

    // Get the password salt & hash from the database result
    $password_hash = $result["password_hash"];
    $password_salt = $result["password_salt"];

    // Ensure that the password hash matches the input + the salt.
    // If it doesn't, redirect the user with a message.
    if($password_hash != hash("SHA512", $password . $password_salt)) {
        header("Location: /userLogin.php?msg=" . urlencode("Incorrect password"), true, 302);
        exit();
    }

    // If we get here, the user exists and gave the correct password.
    // Current admins: Stephen (dev), Kurtis (dev), Nathan (PT)
    $admins = $ini_info["administrators"];
    $admin_emails = array_values($admins);
    if(in_array($email, $admin_emails)) {
        auth\set_auth_level("admin");
    } else {
        auth\set_auth_level("user");
    }

    $_SESSION["user_email"] = $email;

    // Redirect the user and tell them all is well.
    header("Location: /dashboard.php?msg=" . urlencode("Successfully logged in"), true, 302);
}
?>

<!DOCTYPE html>

<html>
    <head>
        <title>Hoop Strength Kelowna</title>
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
            <form method="POST" action="userLogin.php">
                <fieldset>
                    <legend>
                        <h3> Login to Hoop Strength Kelowna's Workout Metric Hub </h3>
                    </legend>

                    <section>
                        <label for="email">Email:</label> <br />
                        <input type="text" name="email" required /> <br/>    
                    </section>

                    <section>
                        <label for="password">Password:</label> <br />
                        <input type="password" name="password" required /> <br />
                    </section>

                    <button type="submit">Log In</button>
                </fieldset>

            </form>
        </main>

        <?php readfile("../components/Footer.html"); ?>
    </body>
</html>
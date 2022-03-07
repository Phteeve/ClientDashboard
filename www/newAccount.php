<?php
require_once("../lib/authentication.php");
require_once("../components/Header.php");

session_start();
auth\check_perms("admin");

// If this isn't a post request, don't bother with business logic
if($_SERVER["REQUEST_METHOD"] == "POST") {
    session_start();

    $dat_ini = parse_ini_file("dat.ini");
    $ini_file = realpath($dat_ini["working_dir"] . "/super_secret_stuff/info.ini");
    $ini_info = parse_ini_file($ini_file);
    
    //connect to server and select database
    $servername = $ini_info["servername"];
    $username = $ini_info["username"];
    $password = $ini_info["password"];
    $dbname = $ini_info["dbname"];

    // Get the data from the user. Don't filter passwords; they aren't going into the database.
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $user_password = $_POST["password"];
    $confirm_password = $_POST["confirmPassword"];
    
    // Make sure we have all the necessary info
    if(!$email || !$user_password || !$confirm_password) {
        header("Location: /newAccount.php?msg=" . urlencode("Missing arguments"), true, 302);
        exit();
    }

    // Make sure the password + confirm password match
    if($user_password != $confirm_password) {
        header("Location: /newAccount.php?msg=" . urlencode("Passwords must match"), true, 302);
        exit();
    }

    $password_salt = bin2hex(random_bytes(64)); // Generate a random salt and get its hex string
    $password_hash = hash("sha512", $user_password . $password_salt); // Generate a password hash using the user's password + the salt

    // Add the user to the database
    $query = "
        INSERT INTO AuthUsers (
            email,
            password_hash,
            password_salt
        ) VALUES (
            '$email',
            '$password_hash',
            '$password_salt'
        )
    ";

    $mysqli = mysqli_connect($servername, $username, $password, $dbname);

    // If something goes wrong, tell the user and log the error
    if(!mysqli_query($mysqli, $query)) {
        $error = mysqli_error($mysqli);
        header("Location: /newAccount.php?msg=" . urlencode("Internal server error"), true, 302);
        exit("[ERROR][MYSQLI] $error");
    }

    mysqli_close($mysqli);

    // Redirect the user and tell them all is well
    header("Location: /newAccount.php?msg=" . urlencode("Successfully created account"), true, 302);
    exit();
}
?>

<html>
    <head>
        <title>Create Account</title>
        <?php readfile("../components/Head.html"); ?>
    </head>
    <body>
        <?php Header::render(); ?>

        <main>
        <form method="POST" action="newAccount.php">
                <fieldset>
                    <legend>
                        <h3> Create an Account </h3>
                    </legend>

                    <section>
                        <label for="email">Email:</label> <br />
                        <input type="text" name="email" required /> <br/>    
                    </section>

                    <section>
                        <label for="password">Password:</label> <br />
                        <input type="password" name="password" required /> <br />
                    </section>

                    <section>
                        <label for="confirmPassword">Confirm Password:</label> <br />
                        <input type="password" name="confirmPassword" required /> <br />
                    </section>

                    <button type="submit">Create Account</button>
                </fieldset>

                <?php
                    if(isset($_GET["msg"])) {
                        echo("<p style='color: red;'>" . htmlspecialchars(urldecode($_GET["msg"])) . "</p>");
                    }
                ?>
            </form>
        </main>

        <?php readfile("../components/Footer.html"); ?>
    </body>
</html>
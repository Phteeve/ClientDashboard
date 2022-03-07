<?php
require_once("../lib/authentication.php");
require_once("../components/Header.php");

session_start();
auth\check_perms("admin");

$dat_ini = parse_ini_file("dat.ini");
$ini_file = realpath($dat_ini["working_dir"] . "/super_secret_stuff/info.ini");
$ini_info = parse_ini_file($ini_file);

//connect to server and select database
$servername = $ini_info["servername"];
$username = $ini_info["username"];
$password = $ini_info["password"];
$dbname = $ini_info["dbname"];

$search = filter_input(INPUT_GET, "search", FILTER_SANITIZE_SPECIAL_CHARS);

// Create connection
$mysqli = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$mysqli) {
  die("Connection failed: " . mysqli_connect_error());
}

// Select unread questions by default
$where = "cq.isRead = 0";

if($search) {
    // If a search query is passed, use it
    $where = "cq.question LIKE '%$search%'";
}

$query = "
    SELECT cq.*, CONCAT(ci.firstName, ' ', ci.lastName) AS name
    FROM ClientQuestions cq
    LEFT JOIN ClientInfo ci
    ON cq.email = ci.email
    WHERE $where
    ORDER BY submitted DESC
";

$res = mysqli_query($mysqli, $query);

$questions = [];
while($row = mysqli_fetch_assoc($res)) {
    $questions[] = $row;
}

mysqli_close($mysqli);
?>
<html>
    <head>
        <title>View Client Questions</title>
        <?php readfile("../components/Head.html"); ?>
    </head>
    <body>
        <?php Header::render(); ?>

        <main>
            <h1>View Questions</h1>
            <?php
				if(isset($_GET["msg"])) {
					echo("<p style='color: red;'>" . htmlspecialchars(urldecode($_GET["msg"])) . "</p>");
				}

                if(count($questions) == 0) {
                    echo("<p style='color: red;'>No unread questions</p>");
                }
			?>

            <form method="GET" action="/viewQuestions.php">
                <label>
                    Search Questions:<br />
                    <input type="search" name="search" />
                </label>
                <br />
                <button type="submit">Search</button>
                <button id="clearSearch">Clear</button>
            </form>

            <table>
                <tr class="head">
                    <td>Name</td>
                    <td>Email</td>
                    <td>Question</td>
                    <td>Date Submitted</td>
                    <td>Mark as Read</td>
                </tr>

                <?php
                    foreach($questions as $q) {
                        echo("<tr>");
                        $name = $q["name"] ?? "Unknown";
                        $email = $q["email"];
                        $question = $q["question"];
                        $submitted = $q["submitted"];
                        $uid = $q["uid"];

                        echo("<td>$name</td>");
                        echo("<td><a href='mailto:$email'>$email</a></td>");
                        echo("<td style='word-break: break-all;'>$question</td>");
                        echo("<td>$submitted</td>");

                        if($q["isRead"]) echo("<td></td>");
                        else echo("<td><button onclick='javascript:markQuestionAsRead($uid)'>Mark as Read</button></td>");

                        echo("</tr>");
                    }
                ?>

            </table>
        </main>

        <?php readfile("../components/Footer.html"); ?>
        <script src="/scripts/viewQuestions.js"></script>
        <script>
            document.getElementById("clearSearch").onclick = clearSearch;
            function clearSearch(e) {
                e.preventDefault();
                location.replace("/viewQuestions.php");
            }
        </script>
    </body>
</html>
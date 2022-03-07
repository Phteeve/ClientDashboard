<?php
require_once("../../lib/authentication.php");

session_start();
auth\check_perms("user");

$dat_ini = parse_ini_file("../dat.ini");
$ini_file = realpath($dat_ini["working_dir"] . "/super_secret_stuff/info.ini");
$ini_info = parse_ini_file($ini_file);

//connect to server and select database
$servername = $ini_info["servername"];
$username = $ini_info["username"];
$password = $ini_info["password"];
$dbname = $ini_info["dbname"];

$mysqli = mysqli_connect($servername, $username, $password, $dbname);

// Get the email to use. Default to session email, but if we're an admin use the passed email
$email = $_SESSION["user_email"];

$arg_email = filter_input(INPUT_GET, "email", FILTER_SANITIZE_EMAIL);
if(auth\has_auth_level("admin") && $arg_email) $email = $arg_email; // If we're an admin, use the given email

$sql = "SELECT * FROM UserData WHERE email = '$email' ORDER BY id";
$result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));

$counter = 0;
while($row = mysqli_fetch_assoc($result)) {
    $height[$counter] = $row['height'];
    $weight[$counter] = $row['weight'];
    $bfp[$counter] = $row['bodyFatPercent'];
    $adf[$counter] = $row['ankleDorsiFlexion'];
    $aslr[$counter] = $row['activeStraightLegRaise'];
    $ihr[$counter] = $row['internalHipRotation'];
    $sor[$counter] = $row['supineOverheadReach'];
    $sjh[$counter] = $row['squatJumpHeight'];
    $rsi[$counter] = $row['reactiveStrengthIndex'];
    $eur[$counter] = $row['eccentricUtilizationRatio'];
    $imtp[$counter] = $row['isometricMidThighPull'];
    $cafta[$counter] = $row['contactAndFlightTimeAsymmetry'];
    //$id[$counter] = $row['id'];
    $counter++;
}

mysqli_close($mysqli);

$label = [];
for ($i = 0; $i < $counter; $i++){
    $label[] = " ";
}

//This is really cringe but I can't figure out how to make this into a for loop without breaking the formatting
$data = [
	"labels" => $label,
	"datasets" => [
		[
			"label" => "Height",
			"data" => $height,
			"backgroundColor" => [
				"#f00"
			]
        ],
        [
            "label" => "Weight",
            "data" => $weight,
            "backgroundColor" => [
				"#f00"
			]
        ],
        [
			"label" => "Body Fat Percentage",
			"data" => $bfp,
			"backgroundColor" => [
				"#f00"
			]
        ],
        [
			"label" => "Ankle Dorsi Flexion",
			"data" => $adf,
			"backgroundColor" => [
				"#f00"
			]
        ],
        [
			"label" => "Active Straight Leg Raise",
			"data" => $aslr,
			"backgroundColor" => [
				"#f00"
			]
        ],
        [
			"label" => "Internal Hip Rotation",
			"data" => $ihr,
			"backgroundColor" => [
				"#f00"
			]
        ],
        [
			"label" => "Supine Overhead Reach",
			"data" => $sor,
			"backgroundColor" => [
				"#f00"
			]
        ],
        [
			"label" => "Squat Jump Height",
			"data" => $sjh,
			"backgroundColor" => [
				"#f00"
			]
        ],
        [
			"label" => "Reactive Strength Index",
			"data" => $rsi,
			"backgroundColor" => [
				"#f00"
			]
        ],
        [
			"label" => "Eccentric Utilization Ratio",
			"data" => $eur,
			"backgroundColor" => [
				"#f00"
			]
        ],
        [
			"label" => "Isometric Mid Thigh Pull",
			"data" => $imtp,
			"backgroundColor" => [
				"#f00"
			]
        ],
        [
			"label" => "Contact And Flight-Time Asymmetry",
			"data" => $cafta,
			"backgroundColor" => [
				"#f00"
			]
        ]
    ]   
];

echo(json_encode($data));

?>
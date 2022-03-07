<?php
    session_start();
    session_unset();
    session_destroy();
    header("Location: /userLogin.php?msg=" . urlencode("Successfully logged out"), true, 302);
    exit;
?>


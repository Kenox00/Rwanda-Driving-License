<?php
// This file can be used for shared functions

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function check_session_timeout() {
    $timeout_duration = 1800; // 30 minutes

    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
        session_unset();
        session_destroy();
        header("location: index.php?message=session_expired");
        exit;
    }
    $_SESSION['LAST_ACTIVITY'] = time();
}

// Check if the user is logged in, otherwise redirect to login page
function require_login() {
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
        header("location: index.php");
        exit;
    }
    check_session_timeout();
}
?>

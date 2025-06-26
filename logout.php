<?php
require_once "functions.php";

// Initialize the session
session_start();

// Clear session data
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page with a logout message
header("location: index.php?message=logout_success");
exit;
?>

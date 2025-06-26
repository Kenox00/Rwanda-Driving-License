<?php
// Check if a session exists before starting one
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error Reporting: Disable for production, enable for development
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'RDL');

// Attempt to connect to MySQL database
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>

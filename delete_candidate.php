<?php
session_start();

require_once "config.php";
require_once "functions.php";
require_login();

if(!isset($_GET["id"])){
    header("location: view_candidates.php?error=" . urlencode("No candidate selected for deletion"));
    exit;
}

$id = $_GET["id"];

try {
    $sql = "DELETE FROM Candidate WHERE CandidateNationalId = ?";

    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "s", $id);
        
        if(mysqli_stmt_execute($stmt)){
            header("location: view_candidates.php?success=" . urlencode("Candidate deleted successfully"));
        } else {
            throw new Exception("Error deleting candidate");
        }
        
        mysqli_stmt_close($stmt);
    }
} catch (Exception $e) {
    header("location: view_candidates.php?error=" . urlencode("Error: " . $e->getMessage()));
}

mysqli_close($link);
?>

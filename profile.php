<?php
require_once "config.php";
require_once "functions.php";
require_login();

$admin_id = $_SESSION['id'];
$admin_name = $_SESSION['adminName'];
$password = $confirm_password = "";
$password_err = $confirm_password_err = "";
$success = $error = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validate password
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";     
    } elseif(strlen(trim($_POST["password"])) < 8){
        $password_err = "Password must have at least 8 characters.";
    } elseif(!preg_match('/[A-Z]/', $_POST["password"])) {
        $password_err = "Password must contain at least one uppercase letter.";
    } elseif(!preg_match('/[a-z]/', $_POST["password"])) {
        $password_err = "Password must contain at least one lowercase letter.";
    } elseif(!preg_match('/[0-9]/', $_POST["password"])) {
        $password_err = "Password must contain at least one number.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm password.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
    }
    
    // Check input errors before updating the database
    if(empty($password_err) && empty($confirm_password_err)){
        // Prepare an update statement
        $sql = "UPDATE Admin SET Password = ? WHERE AdminId = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "si", $param_password, $param_id);
            
            // Set parameters
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_id = $_SESSION["id"];
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Password updated successfully. Destroy the session, and redirect to login page
                $success = "Your password has been changed successfully.";
            } else{
                $error = "Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
}

$page_title = "Admin Profile";
include 'templates/header.php';
include 'templates/sidebar.php';
?>

<div class="page-header">
    <h1><i class="fas fa-user-cog"></i> Admin Profile</h1>
</div>

<div class="card">
    <?php if(!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <?php if(!empty($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

    <div class="profile-info">
        <div class="profile-item">
            <span class="profile-label"><i class="fas fa-id-badge"></i> Admin ID:</span>
            <span class="profile-value"><?php echo $admin_id; ?></span>
        </div>
        <div class="profile-item">
            <span class="profile-label"><i class="fas fa-user"></i> Admin Name:</span>
            <span class="profile-value"><?php echo htmlspecialchars($admin_name); ?></span>
        </div>
    </div>

    <div class="profile-section">
        <h3>Change Password</h3>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"> 
            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label><i class="fas fa-lock"></i> New Password</label>
                <input type="password" name="password" class="form-control" value="<?php echo $password; ?>">
                <small>Password must be at least 8 characters and include uppercase, lowercase, and numbers</small>
                <?php if(!empty($password_err)) echo "<span class='error-message'>$password_err</span>"; ?>
            </div>
            <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                <label><i class="fas fa-lock"></i> Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" value="<?php echo $confirm_password; ?>">
                <?php if(!empty($confirm_password_err)) echo "<span class='error-message'>$confirm_password_err</span>"; ?>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Change Password</button>
            </div>
        </form>
    </div>
</div>

<?php 
include 'templates/footer.php';
mysqli_close($link);
?>

<?php
session_start();

// If user is already logged in, redirect to dashboard
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: dashboard.php");
    exit;
}

require_once "config.php";
require_once "functions.php";

$adminName = $password = $confirm_password = "";
$adminName_err = $password_err = $confirm_password_err = "";
$success = $error = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Validate admin name
    if(empty(trim($_POST["adminName"]))){
        $adminName_err = "Please enter an admin name.";
    } elseif(strlen(trim($_POST["adminName"])) < 3) {
        $adminName_err = "Admin name must be at least 3 characters long.";
    } elseif(!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["adminName"]))) {
        $adminName_err = "Admin name can only contain letters, numbers, and underscores.";
    } else {
        // Check if admin name already exists
        $sql = "SELECT AdminId FROM Admin WHERE AdminName = ?";
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_adminName);
            $param_adminName = trim($_POST["adminName"]);
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $adminName_err = "This admin name is already taken.";
                } else{
                    $adminName = trim($_POST["adminName"]);
                }
            } else{
                $error = "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    
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
    
    // Check input errors before inserting in database
    if(empty($adminName_err) && empty($password_err) && empty($confirm_password_err)){
        
        // Prepare an insert statement
        $sql = "INSERT INTO Admin (AdminName, Password) VALUES (?, ?)";
         
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ss", $param_adminName, $param_password);
            
            // Set parameters
            $param_adminName = $adminName;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                $success = "Registration successful! You can now login with your credentials.";
                // Clear form fields
                $adminName = $password = $confirm_password = "";
            } else{
                $error = "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - RDL Admin</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Changed from login-container to register-container -->
    <div class="register-container">
        <!-- Changed from login-box to register-box -->
        <div class="register-box">
            <h2><i class="fas fa-user-plus"></i> Create Admin Account</h2>
            <p class="login-subtitle">Join the Rwanda Driving License Admin System</p>
            
            <?php if(!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <?php if(!empty($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" novalidate>
                <div class="form-group">
                    <label for="adminName">
                        <i class="fas fa-user"></i> Admin Name
                    </label>
                    <input type="text"
                           id="adminName"
                           name="adminName" 
                           class="form-control <?php echo (!empty($adminName_err)) ? 'input-error' : ''; ?>" 
                           value="<?php echo $adminName; ?>"
                           placeholder="Enter your admin name"
                           required>
                    <small>At least 3 characters, letters, numbers, and underscores only</small>
                    <?php if(!empty($adminName_err)) echo "<span class='error-message'>$adminName_err</span>"; ?>
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" 
                           id="password"
                           name="password" 
                           class="form-control <?php echo (!empty($password_err)) ? 'input-error' : ''; ?>" 
                           value="<?php echo $password; ?>"
                           placeholder="Enter your password"
                           required>
                    <small>At least 8 characters with uppercase, lowercase, and numbers</small>
                    <div class="password-strength" id="passwordStrength" style="display: none;">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <span class="strength-text" id="strengthText"></span>
                    </div>
                    <?php if(!empty($password_err)) echo "<span class='error-message'>$password_err</span>"; ?>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">
                        <i class="fas fa-lock"></i> Confirm Password
                    </label>
                    <input type="password" 
                           id="confirm_password"
                           name="confirm_password" 
                           class="form-control <?php echo (!empty($confirm_password_err)) ? 'input-error' : ''; ?>" 
                           value="<?php echo $confirm_password; ?>"
                           placeholder="Confirm your password"
                           required>
                    <?php if(!empty($confirm_password_err)) echo "<span class='error-message'>$confirm_password_err</span>"; ?>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </div>
            </form>
            
            <div class="login-links">
                <p>Already have an account? <a href="index.php">Login here</a></p>
            </div>
        </div>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>

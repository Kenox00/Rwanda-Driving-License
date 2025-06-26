<?php
require_once 'config.php';
require_once 'functions.php';

session_start();

if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: dashboard.php");
    exit;
}

$adminName = $password = "";
$adminName_err = $password_err = $login_err = "";

// Handle URL messages
if(isset($_GET['message'])) {
    switch($_GET['message']) {
        case 'session_expired':
            $login_err = "Your session has expired. Please log in again.";
            break;
        case 'logout_success':
            $login_err = "You have been successfully logged out.";
            break;
        default:
            break;
    }
}

if($_SERVER["REQUEST_METHOD"] == "POST"){

    if(empty(trim($_POST["adminName"]))){
        $adminName_err = "Please enter admin name.";
    } else{
        $adminName = sanitize_input($_POST["adminName"]);
    }

    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }

    if(empty($adminName_err) && empty($password_err)){
        $sql = "SELECT AdminId, AdminName, Password FROM Admin WHERE AdminName = ?";

        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_adminName);
            $param_adminName = $adminName;

            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);

                if(mysqli_stmt_num_rows($stmt) == 1){
                    mysqli_stmt_bind_result($stmt, $id, $adminName, $hashed_password);
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){
                            session_regenerate_id();

                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["adminName"] = $adminName;
                            $_SESSION['LAST_ACTIVITY'] = time();

                            header("location: dashboard.php");
                        } else{
                            $login_err = "Invalid admin name or password.";
                        }
                    }
                } else{
                    $login_err = "Invalid admin name or password.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($link);
}

$page_title = "Admin Login";
include 'templates/header.php';
?>

<div class="login-container">
    <div class="login-box">
        <h2><i class="fas fa-user-shield"></i> RDL Admin Login</h2>
        <?php 
        if(!empty($login_err)){
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label><i class="fas fa-user"></i> Admin Name</label>
                <input type="text" name="adminName" placeholder="Enter your admin name" value="<?php echo $adminName; ?>" required>
                <span class="help-block"><?php echo $adminName_err; ?></span>
            </div>
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Login</button>
            </div>
        </form>
        
        <div class="login-links">
            <p>Don't have an account? <a href="register.php">Create one here</a></p>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>

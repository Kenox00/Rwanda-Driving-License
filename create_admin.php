<?php
require_once 'config.php';
require_once 'functions.php';
require_login();

$adminName = $password = "";
$adminName_err = $password_err = "";
$success = $error = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(empty(trim($_POST["adminName"]))){
        $adminName_err = "Please enter admin name.";
    } else {
        $adminName = sanitize_input($_POST["adminName"]);
        $sql = "SELECT AdminId FROM Admin WHERE AdminName = ?";
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_adminName);
            $param_adminName = $adminName;
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $adminName_err = "This admin name is already taken.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }

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

    if(empty($adminName_err) && empty($password_err)){
        $sql = "INSERT INTO Admin (AdminName, Password) VALUES (?, ?)";

        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "ss", $param_adminName, $param_password);

            $param_adminName = $adminName;
            $param_password = password_hash($password, PASSWORD_DEFAULT);

            if(mysqli_stmt_execute($stmt)){
                $success = "Admin created successfully!";
                $adminName = $password = ""; // Clear form
            } else{
                $error = "Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($link);
}

$page_title = "Create Admin";
include 'templates/header.php';
include 'templates/sidebar.php';
?>

<div class="page-header">
    <h1><i class="fas fa-user-shield"></i> Create New Admin</h1>
    <a href="dashboard.php" class="btn btn-sm"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
</div>

<div class="card">
    <div class="card-header">
        <h2>Admin Registration Form</h2>
        <p class="text-muted">Create a new admin account (Admin access required)</p>
    </div>
    <div class="card-body">
        <?php if(!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <?php if(!empty($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
        
        <div class="info-message">
            <i class="fas fa-info-circle"></i> This form is for existing admins to create additional admin accounts. 
            For public registration, users should visit the <a href="register.php">registration page</a>.
        </div>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label><i class="fas fa-user"></i> Admin Name</label>
                <input type="text" name="adminName" value="<?php echo $adminName; ?>" required>
                <?php if(!empty($adminName_err)) echo "<span class='error-message'>$adminName_err</span>"; ?>
            </div>
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Password</label>
                <input type="password" name="password" required>
                <small>Password must be at least 8 characters and include uppercase, lowercase, and numbers</small>
                <?php if(!empty($password_err)) echo "<span class='error-message'>$password_err</span>"; ?>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-user-plus"></i> Create Admin</button>
            </div>
        </form>
    </div>
</div>

<?php
include 'templates/footer.php';
?>

<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

require_once "config.php";
require_once "functions.php";
require_login();

$error = '';
$success = '';

if(!isset($_GET["id"]) && !isset($_POST["id"])){
    header("location: view_candidates.php?error=" . urlencode("No candidate selected for editing"));
    exit;
}

$id = isset($_POST["id"]) ? $_POST["id"] : $_GET["id"];

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate form fields
    $validation_errors = [];
    
    // Validate names
    if(empty(trim($_POST["firstName"]))) {
        $validation_errors[] = "Please enter first name.";
    }
    if(empty(trim($_POST["lastName"]))) {
        $validation_errors[] = "Please enter last name.";
    }

    // Validate phone number
    if(empty(trim($_POST["phoneNumber"]))) {
        $validation_errors[] = "Please enter phone number.";
    } elseif(!preg_match('/^07\d{8}$/', trim($_POST["phoneNumber"]))) {
        $validation_errors[] = "Phone number must be in the format 07XXXXXXXX.";
    }

    // Check if phone number already exists but belongs to someone else
    $check_sql = "SELECT CandidateNationalId FROM Candidate WHERE PhoneNumber = ? AND CandidateNationalId != ?";
    if($stmt = mysqli_prepare($link, $check_sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $_POST["phoneNumber"], $id);
        if(mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            if(mysqli_stmt_num_rows($stmt) > 0) {
                $validation_errors[] = "This phone number is already used by another candidate.";
            }
        }
        mysqli_stmt_close($stmt);
    }

    // Validate marks
    if(empty(trim($_POST["obtainedMarks"]))) {
        $validation_errors[] = "Please enter marks.";
    } elseif(!is_numeric($_POST["obtainedMarks"]) || $_POST["obtainedMarks"] < 0 || $_POST["obtainedMarks"] > 20) {
        $validation_errors[] = "Marks must be between 0 and 20.";
    }

    // If validation passed, update the candidate
    if(empty($validation_errors)){
        $decision = (int)$_POST["obtainedMarks"] >= 12 ? "Passed" : "Failed";

        // Start transaction
        mysqli_begin_transaction($link);
        
        try {
            // Update Candidate table
            $sql_candidate = "UPDATE Candidate SET FirstName=?, LastName=?, Gender=?, DOB=?, ExamDate=?, PhoneNumber=? WHERE CandidateNationalId=?";
            if($stmt_candidate = mysqli_prepare($link, $sql_candidate)){
                mysqli_stmt_bind_param($stmt_candidate, "sssssss", 
                    $_POST["firstName"], 
                    $_POST["lastName"], 
                    $_POST["gender"], 
                    $_POST["dob"], 
                    $_POST["examDate"], 
                    $_POST["phoneNumber"], 
                    $id
                );
                mysqli_stmt_execute($stmt_candidate);
                mysqli_stmt_close($stmt_candidate);
                
                // Update Grade table
                $sql_grade = "UPDATE Grade SET LicenseExamCategory=?, ObtainedMarks=?, Decision=? WHERE CandidateNationalId=?";
                if($stmt_grade = mysqli_prepare($link, $sql_grade)){
                    mysqli_stmt_bind_param($stmt_grade, "siss", 
                        $_POST["licenseExamCategory"], 
                        $_POST["obtainedMarks"], 
                        $decision, 
                        $id
                    );
                    mysqli_stmt_execute($stmt_grade);
                    mysqli_stmt_close($stmt_grade);
                }
            }
            
            // Commit transaction
            mysqli_commit($link);
            $success = "Candidate updated successfully!";
            
            // Redirect to view candidates with success message
            header("location: view_candidates.php?success=" . urlencode($success));
            exit;
            
        } catch (Exception $e) {
            // Roll back if something went wrong
            mysqli_rollback($link);
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = implode("<br>", $validation_errors);
    }
} 

// Fetch candidate data
$sql = "SELECT c.*, g.LicenseExamCategory, g.ObtainedMarks FROM Candidate c 
        JOIN Grade g ON c.CandidateNationalId = g.CandidateNationalId 
        WHERE c.CandidateNationalId = ?";
if($stmt = mysqli_prepare($link, $sql)){
    mysqli_stmt_bind_param($stmt, "s", $id);
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        if(mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
        } else {
            header("location: view_candidates.php?error=" . urlencode("Candidate not found"));
            exit;
        }
    } else {
        $error = "Error fetching candidate data.";
    }
    mysqli_stmt_close($stmt);
}

$page_title = "Edit Candidate";
include 'templates/header.php';
include 'templates/sidebar.php';
?>

<div class="page-header">
    <h1><i class="fas fa-user-edit"></i> Edit Candidate</h1>
    <a href="view_candidates.php" class="btn btn-sm"><i class="fas fa-arrow-left"></i> Back to List</a>
</div>

<div class="card">
    <?php if(!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <?php if(!empty($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        
        <div class="form-group">
            <label><i class="fas fa-id-card"></i> Candidate National ID</label>
            <input type="text" value="<?php echo htmlspecialchars($row['CandidateNationalId']); ?>" disabled>
            <small>National ID cannot be changed</small>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-user"></i> First Name</label>
            <input type="text" name="firstName" value="<?php echo htmlspecialchars($row['FirstName']); ?>" required>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-user"></i> Last Name</label>
            <input type="text" name="lastName" value="<?php echo htmlspecialchars($row['LastName']); ?>" required>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-venus-mars"></i> Gender</label>
            <select name="gender" required>
                <option value="Male" <?php if($row['Gender'] == 'Male') echo 'selected'; ?>>Male</option>
                <option value="Female" <?php if($row['Gender'] == 'Female') echo 'selected'; ?>>Female</option>
            </select>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-birthday-cake"></i> Date of Birth</label>
            <input type="date" name="dob" value="<?php echo htmlspecialchars($row['DOB']); ?>" required>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-calendar-alt"></i> Exam Date</label>
            <input type="date" name="examDate" value="<?php echo htmlspecialchars($row['ExamDate']); ?>" required>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-phone"></i> Phone Number</label>
            <input type="text" name="phoneNumber" value="<?php echo htmlspecialchars($row['PhoneNumber']); ?>" required>
            <small>Format: 07XXXXXXXX</small>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-car"></i> License Exam Category</label>
            <select name="licenseExamCategory" required>
                <option value="A" <?php if($row['LicenseExamCategory'] == 'A') echo 'selected'; ?>>Category A (Motorcycle)</option>
                <option value="B" <?php if($row['LicenseExamCategory'] == 'B') echo 'selected'; ?>>Category B (Car)</option>
                <option value="C" <?php if($row['LicenseExamCategory'] == 'C') echo 'selected'; ?>>Category C (Light Truck)</option>
                <option value="D" <?php if($row['LicenseExamCategory'] == 'D') echo 'selected'; ?>>Category D (Bus)</option>
                <option value="E" <?php if($row['LicenseExamCategory'] == 'E') echo 'selected'; ?>>Category E (Heavy Truck)</option>
            </select>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-chart-line"></i> Obtained Marks (out of 20)</label>
            <input type="number" name="obtainedMarks" min="0" max="20" value="<?php echo htmlspecialchars($row['ObtainedMarks']); ?>" required>
            <small>Passing marks: 12 and above</small>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Candidate</button>
        </div>
    </form>
</div>

<?php
include 'templates/footer.php';
mysqli_close($link);
?>

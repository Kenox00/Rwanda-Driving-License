<?php
require_once "config.php";
require_once "functions.php";
require_login();

$success = '';
$error = '';

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate form fields
    $validation_errors = [];
    
    // Validate National ID
    if(empty(trim($_POST["candidateNationalId"]))) {
        $validation_errors[] = "Please enter a candidate national ID.";
    } elseif(strlen(trim($_POST["candidateNationalId"])) != 16 || !ctype_digit(trim($_POST["candidateNationalId"]))) {
        $validation_errors[] = "National ID must be 16 digits.";
    }

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

    // Check if phone number or ID already exists
    $check_sql = "SELECT CandidateNationalId FROM Candidate WHERE CandidateNationalId = ? OR PhoneNumber = ?";
    if($stmt = mysqli_prepare($link, $check_sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $_POST["candidateNationalId"], $_POST["phoneNumber"]);
        if(mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            if(mysqli_stmt_num_rows($stmt) > 0) {
                $validation_errors[] = "A candidate with this National ID or phone number already exists.";
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

    // If validation passed, insert the candidate
    if(empty($validation_errors)){
        $decision = (int)$_POST["obtainedMarks"] >= 12 ? "Passed" : "Failed";

        // Start transaction
        mysqli_begin_transaction($link);
        
        try {
            // Insert into Candidate
            $sql_candidate = "INSERT INTO Candidate (CandidateNationalId, FirstName, LastName, Gender, DOB, ExamDate, PhoneNumber) VALUES (?, ?, ?, ?, ?, ?, ?)";
            if($stmt_candidate = mysqli_prepare($link, $sql_candidate)){
                mysqli_stmt_bind_param($stmt_candidate, "sssssss", 
                    $_POST["candidateNationalId"], 
                    $_POST["firstName"], 
                    $_POST["lastName"], 
                    $_POST["gender"], 
                    $_POST["dob"], 
                    $_POST["examDate"], 
                    $_POST["phoneNumber"]
                );
                mysqli_stmt_execute($stmt_candidate);
                mysqli_stmt_close($stmt_candidate);
                
                // Insert into Grade
                $sql_grade = "INSERT INTO Grade (CandidateNationalId, LicenseExamCategory, ObtainedMarks, Decision) VALUES (?, ?, ?, ?)";
                if($stmt_grade = mysqli_prepare($link, $sql_grade)){
                    mysqli_stmt_bind_param($stmt_grade, "ssis", 
                        $_POST["candidateNationalId"], 
                        $_POST["licenseExamCategory"], 
                        $_POST["obtainedMarks"], 
                        $decision
                    );
                    mysqli_stmt_execute($stmt_grade);
                    mysqli_stmt_close($stmt_grade);
                }
            }
            
            // Commit transaction
            mysqli_commit($link);
            $success = "Candidate added successfully!";
            
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

$page_title = "Add Candidate";
include 'templates/header.php';
include 'templates/sidebar.php';
?>

<div class="page-header">
    <h1>Add New Candidate</h1>
</div>

<div class="card">
    <?php if(!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <?php if(!empty($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group">
            <label><i class="fas fa-id-card"></i> Candidate National ID</label>
            <input type="text" name="candidateNationalId" placeholder="e.g. 1199880012345678" 
                   value="<?php echo isset($_POST['candidateNationalId']) ? htmlspecialchars($_POST['candidateNationalId']) : ''; ?>" required>
            <small>Must be a 16-digit number</small>
        </div>
        <div class="form-group">
            <label><i class="fas fa-user"></i> First Name</label>
            <input type="text" name="firstName" 
                   value="<?php echo isset($_POST['firstName']) ? htmlspecialchars($_POST['firstName']) : ''; ?>" required>
        </div>
        <div class="form-group">
            <label><i class="fas fa-user"></i> Last Name</label>
            <input type="text" name="lastName" 
                   value="<?php echo isset($_POST['lastName']) ? htmlspecialchars($_POST['lastName']) : ''; ?>" required>
        </div>
        <div class="form-group">
            <label><i class="fas fa-venus-mars"></i> Gender</label>
            <select name="gender" required>
                <option value="">-- Select Gender --</option>
                <option value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
            </select>
        </div>
        <div class="form-group">
            <label><i class="fas fa-birthday-cake"></i> Date of Birth</label>
            <input type="date" name="dob" 
                   value="<?php echo isset($_POST['dob']) ? htmlspecialchars($_POST['dob']) : ''; ?>" required>
        </div>
        <div class="form-group">
            <label><i class="fas fa-calendar-alt"></i> Exam Date</label>
            <input type="date" name="examDate" 
                   value="<?php echo isset($_POST['examDate']) ? htmlspecialchars($_POST['examDate']) : ''; ?>" required>
        </div>
        <div class="form-group">
            <label><i class="fas fa-phone"></i> Phone Number</label>
            <input type="text" name="phoneNumber" placeholder="e.g. 0788123456" 
                   value="<?php echo isset($_POST['phoneNumber']) ? htmlspecialchars($_POST['phoneNumber']) : ''; ?>" required>
            <small>Format: 07XXXXXXXX</small>
        </div>
        <div class="form-group">
            <label><i class="fas fa-car"></i> License Exam Category</label>
            <select name="licenseExamCategory" required>
                <option value="">-- Select Category --</option>
                <option value="A" <?php echo (isset($_POST['licenseExamCategory']) && $_POST['licenseExamCategory'] == 'A') ? 'selected' : ''; ?>>Category A (Motorcycle)</option>
                <option value="B" <?php echo (isset($_POST['licenseExamCategory']) && $_POST['licenseExamCategory'] == 'B') ? 'selected' : ''; ?>>Category B (Car)</option>
                <option value="C" <?php echo (isset($_POST['licenseExamCategory']) && $_POST['licenseExamCategory'] == 'C') ? 'selected' : ''; ?>>Category C (Light Truck)</option>
                <option value="D" <?php echo (isset($_POST['licenseExamCategory']) && $_POST['licenseExamCategory'] == 'D') ? 'selected' : ''; ?>>Category D (Bus)</option>
                <option value="E" <?php echo (isset($_POST['licenseExamCategory']) && $_POST['licenseExamCategory'] == 'E') ? 'selected' : ''; ?>>Category E (Heavy Truck)</option>
            </select>
        </div>
        <div class="form-group">
            <label><i class="fas fa-chart-line"></i> Obtained Marks (out of 20)</label>
            <input type="number" name="obtainedMarks" min="0" max="20" 
                   value="<?php echo isset($_POST['obtainedMarks']) ? htmlspecialchars($_POST['obtainedMarks']) : ''; ?>" required>
            <small>Passing marks: 12 and above</small>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Candidate</button>
        </div>
    </form>
</div>

<?php
include 'templates/footer.php';
mysqli_close($link);
?>

<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

require_once "config.php";
require_once "functions.php";
require_login();

// Handle search and filtering
$where_clause = "";
$params = [];
$types = "";

if(isset($_GET['search']) && !empty($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $where_clause = " WHERE c.CandidateNationalId LIKE ? OR c.FirstName LIKE ? OR c.LastName LIKE ? OR c.PhoneNumber LIKE ?";
    $params = [$search, $search, $search, $search];
    $types = "ssss";
}

if(isset($_GET['category']) && !empty($_GET['category'])) {
    $where_clause = $where_clause ? $where_clause . " AND g.LicenseExamCategory = ?" : " WHERE g.LicenseExamCategory = ?";
    $params[] = $_GET['category'];
    $types .= "s";
}

if(isset($_GET['decision']) && !empty($_GET['decision'])) {
    $where_clause = $where_clause ? $where_clause . " AND g.Decision = ?" : " WHERE g.Decision = ?";
    $params[] = $_GET['decision'];
    $types .= "s";
}

// Prepare and execute query
$sql = "SELECT c.CandidateNationalId, c.FirstName, c.LastName, c.Gender, c.DOB, c.ExamDate, c.PhoneNumber, g.LicenseExamCategory, g.ObtainedMarks, g.Decision 
        FROM Candidate c JOIN Grade g ON c.CandidateNationalId = g.CandidateNationalId" . $where_clause;

$result = null;

if(empty($params)) {
    $result = mysqli_query($link, $sql);
} else {
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
}

// Get all unique categories for the filter dropdown
$categories_query = "SELECT DISTINCT LicenseExamCategory FROM Grade ORDER BY LicenseExamCategory";
$categories_result = mysqli_query($link, $categories_query);

$page_title = "View Candidates";
include 'templates/header.php';
include 'templates/sidebar.php';
?>

<div class="page-header">
    <h1><i class="fas fa-users"></i> All Candidates</h1>
    <a href="add_candidate.php" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add New Candidate</a>
</div>

<div class="card">
    <div class="search-filter-box">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="d-flex">
            <input type="text" name="search" id="search-input" placeholder="Search by ID, name or phone..." 
                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            
            <select name="category">
                <option value="">All Categories</option>
                <?php while($cat = mysqli_fetch_assoc($categories_result)): ?>
                    <option value="<?php echo htmlspecialchars($cat['LicenseExamCategory']); ?>" 
                            <?php if(isset($_GET['category']) && $_GET['category'] == $cat['LicenseExamCategory']) echo 'selected'; ?>>
                        Category <?php echo htmlspecialchars($cat['LicenseExamCategory']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            
            <select name="decision">
                <option value="">All Results</option>
                <option value="Passed" <?php if(isset($_GET['decision']) && $_GET['decision'] == 'Passed') echo 'selected'; ?>>Passed</option>
                <option value="Failed" <?php if(isset($_GET['decision']) && $_GET['decision'] == 'Failed') echo 'selected'; ?>>Failed</option>
            </select>
            
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filter</button>
            <a href="view_candidates.php" class="btn btn-sm"><i class="fas fa-redo"></i> Reset</a>
        </form>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th data-sort>National ID</th>
                    <th data-sort>Name</th>
                    <th data-sort>Gender</th>
                    <th data-sort>DOB</th>
                    <th data-sort>Exam Date</th>
                    <th data-sort>Phone</th>
                    <th data-sort>Category</th>
                    <th data-sort>Marks</th>
                    <th data-sort>Decision</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result && mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['CandidateNationalId']); ?></td>
                        <td><?php echo htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']); ?></td>
                        <td><?php echo htmlspecialchars($row['Gender']); ?></td>
                        <td><?php echo htmlspecialchars($row['DOB']); ?></td>
                        <td><?php echo htmlspecialchars($row['ExamDate']); ?></td>
                        <td><?php echo htmlspecialchars($row['PhoneNumber']); ?></td>
                        <td><?php echo htmlspecialchars($row['LicenseExamCategory']); ?></td>
                        <td><?php echo htmlspecialchars($row['ObtainedMarks']); ?></td>
                        <td>
                            <span class="<?php echo $row['Decision'] == 'Passed' ? 'text-success' : 'text-danger'; ?>">
                                <?php echo $row['Decision'] == 'Passed' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-times-circle"></i>'; ?>
                                <?php echo htmlspecialchars($row['Decision']); ?>
                            </span>
                        </td>
                        <td class="action-links">
                            <a href="edit_candidate.php?id=<?php echo $row['CandidateNationalId']; ?>" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete_candidate.php?id=<?php echo $row['CandidateNationalId']; ?>" class="delete" title="Delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="text-center">No candidates found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
include 'templates/footer.php';
mysqli_close($link);
?>

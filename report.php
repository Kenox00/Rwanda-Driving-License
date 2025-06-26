<?php
require_once "config.php";
require_once "functions.php";
require_login();

// Initialize variables
$where_conditions = [];
$params = [];
$types = "";
$search_term = "";

// Handle search functionality
if(isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_term = trim($_GET['search']);
    $search_param = "%" . $search_term . "%";
    $where_conditions[] = "(c.CandidateNationalId LIKE ? OR c.FirstName LIKE ? OR c.LastName LIKE ?)";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
    $types .= "sss";
}

// Handle category filtering
if(isset($_GET['category']) && !empty($_GET['category'])) {
    $where_conditions[] = "g.LicenseExamCategory = ?";
    $params[] = $_GET['category'];
    $types .= "s";
}

// Handle decision filtering
if(isset($_GET['decision']) && !empty($_GET['decision'])) {
    $where_conditions[] = "g.Decision = ?";
    $params[] = $_GET['decision'];
    $types .= "s";
}

// Handle date range filtering
if(isset($_GET['date_from']) && !empty($_GET['date_from'])) {
    $where_conditions[] = "c.ExamDate >= ?";
    $params[] = $_GET['date_from'];
    $types .= "s";
}

if(isset($_GET['date_to']) && !empty($_GET['date_to'])) {
    $where_conditions[] = "c.ExamDate <= ?";
    $params[] = $_GET['date_to'];
    $types .= "s";
}

// Build WHERE clause
$where_clause = "";
if (!empty($where_conditions)) {
    $where_clause = " WHERE " . implode(" AND ", $where_conditions);
}

// Base SQL query
$sql = "SELECT c.CandidateNationalId, c.FirstName, c.LastName, c.ExamDate, g.LicenseExamCategory, g.ObtainedMarks, g.Decision 
        FROM Candidate c 
        JOIN Grade g ON c.CandidateNationalId = g.CandidateNationalId" . $where_clause . 
        " ORDER BY c.ExamDate DESC, g.LicenseExamCategory";

$result = null;

try {
    if(empty($params)) {
        $result = mysqli_query($link, $sql);
        if (!$result) {
            throw new Exception("Query failed: " . mysqli_error($link));
        }
    } else {
        $stmt = mysqli_prepare($link, $sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($link));
        }
        
        if (!mysqli_stmt_bind_param($stmt, $types, ...$params)) {
            throw new Exception("Bind failed: " . mysqli_stmt_error($stmt));
        }
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
        }
        
        $result = mysqli_stmt_get_result($stmt);
        if (!$result) {
            throw new Exception("Get result failed: " . mysqli_stmt_error($stmt));
        }
        
        mysqli_stmt_close($stmt);
    }
} catch (Exception $e) {
    error_log("Report filtering error: " . $e->getMessage());
    $error_message = "An error occurred while filtering the report. Please try again.";
}

// Get stats
$stats = [
    'total' => 0,
    'passed' => 0,
    'failed' => 0,
    'pass_rate' => 0,
    'categories' => []
];

// Get all unique categories for the filter dropdown
$categories_query = "SELECT DISTINCT LicenseExamCategory FROM Grade ORDER BY LicenseExamCategory";
$categories_result = mysqli_query($link, $categories_query);

$categories = [];
while($cat = mysqli_fetch_assoc($categories_result)) {
    $categories[] = $cat['LicenseExamCategory'];
}

// Calculate stats if results exist
if($result && mysqli_num_rows($result) > 0) {
    $stats['total'] = mysqli_num_rows($result);
    
    // We need to create a copy of the result set for stats since we'll use the original to display data
    $stats_result = mysqli_query($link, $sql);
    
    while($row = mysqli_fetch_assoc($stats_result)) {
        if($row['Decision'] == 'Passed') {
            $stats['passed']++;
        } else {
            $stats['failed']++;
        }
        
        // Count by category
        if(!isset($stats['categories'][$row['LicenseExamCategory']])) {
            $stats['categories'][$row['LicenseExamCategory']] = [
                'total' => 0,
                'passed' => 0,
                'failed' => 0
            ];
        }
        
        $stats['categories'][$row['LicenseExamCategory']]['total']++;
        if($row['Decision'] == 'Passed') {
            $stats['categories'][$row['LicenseExamCategory']]['passed']++;
        } else {
            $stats['categories'][$row['LicenseExamCategory']]['failed']++;
        }
    }
    
    // Calculate pass rate
    $stats['pass_rate'] = $stats['total'] > 0 ? round(($stats['passed'] / $stats['total']) * 100, 1) : 0;
    
    // Calculate pass rates by category
    foreach($stats['categories'] as $category => $data) {
        $stats['categories'][$category]['pass_rate'] = $data['total'] > 0 ? round(($data['passed'] / $data['total']) * 100, 1) : 0;
    }
}

$page_title = "Exam Results Report";
include 'templates/header.php';
include 'templates/sidebar.php';
?>

<div class="page-header">
    <h1><i class="fas fa-chart-bar"></i> Exam Results Report</h1>
</div>

<div class="card">
    <?php if(isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>
    
    <div class="search-filter-box">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="d-flex">
            <input type="text" 
                   name="search" 
                   id="search-input" 
                   placeholder="Search by ID, Name..." 
                   value="<?php echo htmlspecialchars($search_term); ?>">
                   
            <select name="category">
                <option value="">All Categories</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>" 
                            <?php if(isset($_GET['category']) && $_GET['category'] == $cat) echo 'selected'; ?>>
                        Category <?php echo htmlspecialchars($cat); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select name="decision">
                <option value="">All Results</option>
                <option value="Passed" <?php if(isset($_GET['decision']) && $_GET['decision'] == 'Passed') echo 'selected'; ?>>Passed</option>
                <option value="Failed" <?php if(isset($_GET['decision']) && $_GET['decision'] == 'Failed') echo 'selected'; ?>>Failed</option>
            </select>
            
            <input type="date" 
                   name="date_from" 
                   placeholder="From Date" 
                   title="Filter from date" 
                   value="<?php echo isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : ''; ?>">
            
            <input type="date" 
                   name="date_to" 
                   placeholder="To Date" 
                   title="Filter to date"
                   value="<?php echo isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : ''; ?>">
            
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-filter"></i> Filter
            </button>
            <a href="report.php" class="btn btn-sm">
                <i class="fas fa-redo"></i> Reset
            </a>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-cards">
        <div class="stats-card total">
            <h3>Total Candidates</h3>
            <p class="stat-number"><?php echo $stats['total']; ?></p>
        </div>
        
        <div class="stats-card passed">
            <h3>Passed</h3>
            <p class="stat-number"><?php echo $stats['passed']; ?></p>
        </div>
        
        <div class="stats-card failed">
            <h3>Failed</h3>
            <p class="stat-number"><?php echo $stats['failed']; ?></p>
        </div>
        
        <div class="stats-card pass-rate">
            <h3>Pass Rate</h3>
            <p class="stat-number"><?php echo $stats['pass_rate']; ?>%</p>
        </div>
    </div>

    <!-- Category Statistics -->
    <div class="category-stats">
        <h3>Statistics by Category</h3>
        <table class="stats-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Total</th>
                    <th>Passed</th>
                    <th>Failed</th>
                    <th>Pass Rate</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($stats['categories'] as $category => $data): ?>
                <tr>
                    <td>Category <?php echo htmlspecialchars($category); ?></td>
                    <td><?php echo $data['total']; ?></td>
                    <td><?php echo $data['passed']; ?></td>
                    <td><?php echo $data['failed']; ?></td>
                    <td><?php echo $data['pass_rate']; ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Detailed Results Table -->
    <h3>Detailed Exam Results</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th data-sort>National ID</th>
                    <th data-sort>Name</th>
                    <th data-sort>Exam Date</th>
                    <th data-sort>Category</th>
                    <th data-sort>Marks</th>
                    <th data-sort>Decision</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result && mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['CandidateNationalId']); ?></td>
                        <td><?php echo htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']); ?></td>
                        <td><?php echo htmlspecialchars($row['ExamDate']); ?></td>
                        <td>Category <?php echo htmlspecialchars($row['LicenseExamCategory']); ?></td>
                        <td><?php echo htmlspecialchars($row['ObtainedMarks']) . '/20'; ?></td>
                        <td>
                            <span class="<?php echo $row['Decision'] == 'Passed' ? 'text-success' : 'text-danger'; ?>">
                                <?php echo $row['Decision'] == 'Passed' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-times-circle"></i>'; ?>
                                <?php echo htmlspecialchars($row['Decision']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No results found</td>
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

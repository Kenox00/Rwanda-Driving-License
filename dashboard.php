<?php
require_once 'config.php';
require_once 'functions.php';
require_login();

$page_title = "Admin Dashboard";
include 'templates/header.php';
include 'templates/sidebar.php';
?>

    <div class="page-header">
        <h1><i class="fas fa-tachometer-alt"></i> Welcome, <?php echo htmlspecialchars($_SESSION["adminName"]); ?>!</h1>
        <p class="page-subtitle">Rwanda Driving License Admin Dashboard</p>
    </div>

    <div class="dashboard-stats">
        <?php
        // Get statistics
        $total_candidates = 0;
        $passed_candidates = 0;
        $failed_candidates = 0;
        $recent_exams = 0;

        // Count total candidates
        $sql = "SELECT COUNT(*) as total FROM Candidate";
        if($result = mysqli_query($link, $sql)) {
            $row = mysqli_fetch_assoc($result);
            $total_candidates = $row['total'];
        }

        // Count passed/failed
        $sql = "SELECT Decision, COUNT(*) as count FROM Grade GROUP BY Decision";
        if($result = mysqli_query($link, $sql)) {
            while($row = mysqli_fetch_assoc($result)) {
                if($row['Decision'] == 'Passed') {
                    $passed_candidates = $row['count'];
                } else {
                    $failed_candidates = $row['count'];
                }
            }
        }

        // Count recent exams (last 30 days)
        $sql = "SELECT COUNT(*) as recent FROM Candidate WHERE ExamDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        if($result = mysqli_query($link, $sql)) {
            $row = mysqli_fetch_assoc($result);
            $recent_exams = $row['recent'];
        }
        ?>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $total_candidates; ?></h3>
                <p>Total Candidates</p>
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $passed_candidates; ?></h3>
                <p>Passed</p>
            </div>
        </div>

        <div class="stat-card danger">
            <div class="stat-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $failed_candidates; ?></h3>
                <p>Failed</p>
            </div>
        </div>

        <div class="stat-card info">
            <div class="stat-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $recent_exams; ?></h3>
                <p>Recent Exams (30 days)</p>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-info-circle"></i> Quick Actions</h2>
        </div>
        <div class="card-body">
            <p>Welcome to the Rwanda Driving License Admin Dashboard. Use the navigation menu to manage candidates and view reports.</p>
            <div class="button-group">
                <a href="add_candidate.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Add New Candidate
                </a>
                <a href="view_candidates.php" class="btn btn-secondary">
                    <i class="fas fa-users"></i> View All Candidates
                </a>
                <a href="report.php" class="btn btn-info">
                    <i class="fas fa-chart-bar"></i> View Reports
                </a>
            </div>
        </div>
    </div>

    <?php include 'templates/footer.php'; ?>

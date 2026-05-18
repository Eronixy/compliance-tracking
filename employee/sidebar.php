<head>
    <?php include('../includes/header.php'); ?>
</head>

<div class="sidebar"> 

    <?php
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $username = !empty($_SESSION['username']) ? $_SESSION['username'] : 'User';
    $firstName = explode(' ', trim($username))[0] ?? $username;
    
    $currentPage = basename($_SERVER['PHP_SELF']);
    ?>

    <!-- Top Section -->
    <div class="sidebar-top">
        
        <div class="sidebar-brand">
            <div class="sidebar-logo">
                <img src="../shared/img/LogoBlack.svg" alt="Logo Black" />
            </div>
            <span class="sidebar-name">ano name</span>
        </div>
        <div class="sidebar-subtitle">Compliance Tracking</div>

        <ul class="sidebar-nav">
            <li><a class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php"><img src="../shared/img/Dashboard.svg" alt="Dashboard icon"><span>Dashboard</span></a></li>
            <li><a class="<?= $currentPage === 'activity-logs.php' ? 'active' : '' ?>" href="activity-logs.php"><img src="../shared/img/ActLogs.svg" alt="Activity Logs icon"><span>Activity Logs</span></a></li>
            <li><a class="<?= $currentPage === 'tasks.php' ? 'active' : '' ?>" href="tasks.php"><img src="../shared/img/Tasks.svg" alt="Tasks icon"><span>Tasks</span></a></li>
            <li><a class="<?= $currentPage === 'submit-report.php' ? 'active' : '' ?>" href="submit-report.php"><img src="../shared/img/Submit Report.svg" alt="Submit Report icon"><span>Submit Report</span></a></li>
            <li><a class="<?= $currentPage === 'compliance.php' ? 'active' : '' ?>" href="compliance.php"><img src="../shared/img/Compliance.svg" alt="Compliance icon"><span>Compliance</span></a></li>
            <li><a class="<?= $currentPage === 'my-reports.php' ? 'active' : '' ?>" href="my-reports.php"><img src="../shared/img/My reports.svg" alt="My Reports icon"><span>My Reports</span></a></li>
        </ul>
    </div>

    <!-- Bottom Section -->
    <div class="sidebar-bottom">
        
        <hr class="sidebar-divider">
        
        <!-- Report Button -->
        <a href="submit-report.php" class="btn-new-report">New Report</a>
        
        <!-- Bottom Links -->
        <a class="sidebar-action <?= $currentPage === 'edit-account.php' ? 'active' : '' ?>" href="edit-account.php">
            <img src="../shared/img/Edit Account.svg" alt="Edit Account icon">
            <span>Edit Account</span>
        </a>
        <a class="sidebar-action" href="../includes/logout.php">
            <img src="../shared/img/Logout.svg" alt="Logout icon">
            <span>Logout</span>
        </a>
    </div>
</div>
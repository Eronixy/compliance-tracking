<head>
<?php include('../includes/headeradmin.php'); ?>
<?php include('../includes/headeremployee.php'); ?>
</head>

<div class="sidebar">

    <?php
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $username = !empty($_SESSION['username']) ? $_SESSION['username'] : 'Admin';
    $firstName = explode(' ', trim($username))[0] ?? $username;
    
    $currentPage = basename($_SERVER['PHP_SELF']);
    ?>

    <!-- Top Section -->
    <div class="sidebar-top">
        
        <div class="sidebar-brand">
            <div class="sidebar-logo">
                <img src="../shared/img/LogoBlack.svg" alt="Logo Black" />
            </div>
            <span class="sidebar-name"><?= htmlspecialchars($firstName) ?></span>
        </div>
        <div class="sidebar-subtitle">Admin Panel</div>

        <ul class="sidebar-nav">
            <li><a class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php"><img src="../shared/img/Home.svg" alt="Dashboard icon"><span>Home</span></a></li>
            <li><a class="<?= $currentPage === 'users.php' ? 'active' : '' ?>" href="users.php"><img src="../shared/img/usermanagement.svg" alt="User Management icon"><span>User Management</span></a></li>
            <li><a class="<?= $currentPage === 'compliance.php' ? 'active' : '' ?>" href="compliance.php"><img src="../shared/img/Compliance.svg" alt="Compliance icon"><span>Compliance</span></a></li>
            <li><a class="<?= $currentPage === 'policies.php' ? 'active' : '' ?>" href="policies.php"><img src="../shared/img/policies.svg" alt="Policies icon"><span>Policies</span></a></li>
            <li><a class="<?= $currentPage === 'reports.php' ? 'active' : '' ?>" href="reports.php"><img src="../shared/img/reports&analytics.svg" alt="Reports icon"><span>Reports & Analytics</span></a></li>
            <li><a class="<?= $currentPage === 'incidents.php' ? 'active' : '' ?>" href="incidents.php"><img src="../shared/img/incidentreports.svg" alt="Incident Reports icon"><span>Incident Reports</span></a></li>
            <li><a class="<?= $currentPage === 'threats.php' ? 'active' : '' ?>" href="threats.php"><img src="../shared/img/threatalerts.svg" alt="Threat Alerts icon"><span>Threat Alerts</span></a></li>
            <li><a class="<?= $currentPage === 'violations.php' ? 'active' : '' ?>" href="violations.php"><img src="../shared/img/violations.svg" alt="Violations icon"><span>Violations</span></a></li>
            <li><a class="<?= $currentPage === 'audit-logs.php' ? 'active' : '' ?>" href="audit-logs.php"><img src="../shared/img/auditlogs.svg" alt="Audit Logs icon"><span>Audit Logs</span></a></li>
        </ul>
    </div>

    <!-- Bottom Section -->
    <div class="sidebar-bottom">
        
        <hr class="sidebar-divider">
        
        <!-- Bottom Links -->
        <a class="sidebar-action" href="../includes/logout.php">
            <img src="../shared/img/Logout.svg" alt="Logout icon">
            <span>Logout</span>
        </a>
    </div>
</div>
<?php
session_start();
include(__DIR__ . '/../config/connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'employee') {
    header("Location: ../index.php");
    exit();
}

$userID = $_SESSION['id'];

$query = mysqli_query($conn, "
    SELECT * FROM activity_logs
    WHERE user_id='$userID'
    ORDER BY log_time DESC
");
?>

<!DOCTYPE html>
<html>

<head>
    <?php include('../includes/headeremployee.php'); ?>
</head>

<body style="    
    padding-top: 0px;
    padding-left: 0px;
    padding-right: 0px;
    padding-bottom: 0px;">

    <div class="app-layout">

        <?php include('sidebar.php'); ?>

        <div class="main-wrapper-activity">

            <!-- Header -->
            <div class="top-bar">
                <h1>Activity Logs</h1>
            </div>

            <!-- Scrollable Content Area -->
            <div class="content-body">

                <h1 class="page-title">Activity Timeline</h1>
                <p class="page-subtitle">
                    Track all your actions in the system. Monitor submissions, compliance updates, and security logs in
                    chronological order.
                </p>

                <div class="timeline">

                    <?php if (mysqli_num_rows($query) > 0) { ?>
                        <?php while ($row = mysqli_fetch_assoc($query)) { ?>

                            <div class="timeline-item">
                                <div class="timeline-dot"></div>

                                <!-- The Timeline Box -->
                                <div class="timeline-card">
                                    <div class="card-content">
                                        <!-- Tiny Clock Icon -->
                                        <svg class="card-icon" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                            stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>

                                        <!-- Action Text -->
                                        <span class="card-text">
                                            <?= htmlspecialchars($row['action']) ?>
                                            <?php if (!empty($row['task_id'])) { ?>
                                                (Task #<?= $row['task_id'] ?>)
                                            <?php } ?>
                                        </span>

                                        <!-- Timestamp -->
                                        <span class="card-time">
                                            <?= date('M d, Y h:i A', strtotime($row['log_time'])) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                        <?php } ?>
                    <?php } else { ?>
                        <p class="empty-state">No activity found.</p>
                    <?php } ?>

                </div>
            </div>

        </div>
    </div>

</body>

</html>
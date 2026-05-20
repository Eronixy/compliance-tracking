<?php
session_start();
include(__DIR__ . '/../config/connect.php');

if ($_SESSION['role'] != 'employee') {
    header("Location: index.php");
    exit();
}

$userID = $_SESSION['id'];
$name = $_SESSION['username'];
$firstName = explode(' ', trim($name))[0] ?? $name;

/* Compliance */
$totalPolicies = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM compliance_records WHERE user_id='$userID'"));
$compliant = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM compliance_records WHERE user_id='$userID' AND compliance_status='Compliant'"));
$pending = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM compliance_records WHERE user_id='$userID' AND compliance_status='Pending'"));
$nonCompliant = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM compliance_records WHERE user_id='$userID' AND compliance_status='Non-Compliant'"));
$rate = ($totalPolicies > 0) ? ($compliant / $totalPolicies) * 100 : 0;

/* Tasks */
$totalTasks = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tasks WHERE assigned_to='$userID'"));
$completedTasks = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tasks WHERE assigned_to='$userID' AND status='Completed'"));

/* Incidents */
$totalIncidents = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM incident_reports WHERE user_id='$userID'"));
$pendingIncidents = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM incident_reports WHERE user_id='$userID' AND status='Pending'"));

/* Violations */
$violations = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM violations WHERE user_id='$userID'"));

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html>

<head>
    <?php include('../includes/headeremployee.php'); ?>
</head>

<body>
    <div class="app-layout">
        <?php include('sidebar.php'); ?>

        <div class="main-wrapper-dashboard">

            <!-- Header -->
            <div class="top-bar">
                <h1>Compliance Overview</h1>
            </div>

            <!-- Scrollable Dashboard Body -->
            <div class="content-body">

                <div class="hero-card">
                    <div class="hero-content">
                        <h2>Welcome, <?= htmlspecialchars($firstName) ?></h2>
                        <p>Here is your daily compliance and risk overview. You have tasks and incidents that need your
                            attention.</p>
                    </div>
                    <div class="chart-container">
                        <div class="chart-ring"
                            style="background: conic-gradient(#0A58CA <?= round($rate) ?>%, #E2ECF7 0);">
                            <div class="chart-inner">
                                <span class="chart-perc"><?= round($rate) ?>%</span>
                                <span class="chart-label">RATE</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Pills -->
                <div class="stats-grid">

                    <div class="stat-pill">
                        <span class="stat-text">Compliant</span>
                        <div class="stat-icon icon-green">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#a3e6cd"
                                stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                    </div>

                    <div class="stat-pill">
                        <span class="stat-text">Pending</span>
                        <div class="stat-icon icon-orange">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fbd38d"
                                stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="5" cy="12" r="1"></circle>
                                <circle cx="12" cy="12" r="1"></circle>
                                <circle cx="19" cy="12" r="1"></circle>
                            </svg>
                        </div>
                    </div>

                    <div class="stat-pill">
                        <span class="stat-text">Violations</span>
                        <div class="stat-icon icon-red">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#feb2b2"
                                stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                        </div>
                    </div>

                    <div class="stat-pill">
                        <span class="stat-text">Tasks</span>
                        <div class="stat-icon icon-green">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#a3e6cd"
                                stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                    </div>

                    <div class="stat-pill">
                        <span class="stat-text">Incidents</span>
                        <div class="stat-icon icon-orange">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fbd38d"
                                stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="5" cy="12" r="1"></circle>
                                <circle cx="12" cy="12" r="1"></circle>
                                <circle cx="19" cy="12" r="1"></circle>
                            </svg>
                        </div>
                    </div>

                </div>

                <!-- Compliance Status Full Width Table -->
                <div class="table-box">
                    <h3 class="section-title">Compliance Status</h3>
                    <table class="custom-table">
                        <tr>
                            <th>Policy</th>
                            <th>Description</th>
                            <th>Last Updated</th>
                            <th style="text-align:right;">Status</th>
                        </tr>
                        <?php
                        $q = mysqli_query($conn, "SELECT compliance_records.*, policies.policy_name FROM compliance_records INNER JOIN policies ON compliance_records.policy_id = policies.id WHERE compliance_records.user_id='$userID'");
                        if (mysqli_num_rows($q) > 0) {
                            while ($r = mysqli_fetch_assoc($q)) {
                                echo "<tr>
                                <td>{$r['policy_name']}</td>
                                <td>-</td>
                                <td>{$r['updated_at']}</td>
                                <td style='text-align:right;'>{$r['compliance_status']}</td>
                            </tr>";
                            }
                        } else {
                            // Empty row styling exactly like picture
                            echo "<tr><td></td><td></td><td></td><td style='text-align:right;'><div class='badge-yellow'></div></td></tr>";
                            echo "<tr><td colspan='4' class='empty-state'>No records found.</td></tr>";
                        }
                        ?>
                    </table>
                </div>

                <!-- Two Column Tables (Tasks & Incidents) -->
                <div class="tables-row">

                    <div class="table-box">
                        <h3 class="section-title">My Tasks</h3>
                        <table class="custom-table">
                            <tr>
                                <th>Task</th>
                                <th>Priority</th>
                                <th>Deadline</th>
                                <th style="text-align:right;">Status</th>
                            </tr>
                            <?php
                            $t = mysqli_query($conn, "SELECT * FROM tasks WHERE assigned_to='$userID'");
                            if (mysqli_num_rows($t) > 0) {
                                while ($row = mysqli_fetch_assoc($t)) {
                                    echo "<tr>
                                    <td>{$row['title']}</td>
                                    <td>{$row['priority']}</td>
                                    <td>{$row['deadline']}</td>
                                    <td style='text-align:right;'>{$row['status']}</td>
                                </tr>";
                                }
                            } else {
                                echo "<tr><td></td><td></td><td></td><td style='text-align:right;'><div class='badge-yellow'></div></td></tr>";
                                echo "<tr><td colspan='4' class='empty-state'>No Tasks found.</td></tr>";
                            }
                            ?>
                        </table>
                    </div>

                    <div class="table-box">
                        <h3 class="section-title">My Incident Reports</h3>
                        <table class="custom-table">
                            <tr>
                                <th>Title</th>
                                <th>Severity</th>
                                <th>Date</th>
                                <th style="text-align:right;">Status</th>
                            </tr>
                            <?php
                            $i = mysqli_query($conn, "SELECT * FROM incident_reports WHERE user_id='$userID' ORDER BY id DESC");
                            if (mysqli_num_rows($i) > 0) {
                                while ($r = mysqli_fetch_assoc($i)) {
                                    echo "<tr>
                                    <td>{$r['title']}</td>
                                    <td>{$r['severity']}</td>
                                    <td>{$r['date_reported']}</td>
                                    <td style='text-align:right;'>{$r['status']}</td>
                                </tr>";
                                }
                            } else {
                                echo "<tr><td></td><td></td><td></td><td style='text-align:right;'><div class='badge-yellow'></div></td></tr>";
                                echo "<tr><td colspan='4' class='empty-state'>No Tasks found.</td></tr>";
                            }
                            ?>
                        </table>
                    </div>

                </div>

                <!-- Activity Logs Full Width Table -->
                <div class="table-box">
                    <h3 class="section-title">Activity Logs</h3>
                    <table class="custom-table" style="margin-bottom: 40px;">
                        <tr>
                            <th>Action</th>
                            <th style="text-align:right;">Date</th>
                        </tr>
                        <?php
                        $l = mysqli_query($conn, "SELECT * FROM activity_logs WHERE user_id='$userID' ORDER BY log_time DESC");
                        if (mysqli_num_rows($l) > 0) {
                            while ($log = mysqli_fetch_assoc($l)) {
                                echo "<tr>
                                <td>{$log['action']}</td>
                                <td style='text-align:right;'>{$log['log_time']}</td>
                            </tr>";
                            }
                        } else {
                            echo "<tr><td></td><td style='text-align:right;'></td></tr>";
                            echo "<tr><td colspan='2' class='empty-state'>No activity found.</td></tr>";
                        }
                        ?>
                    </table>
                </div>

            </div> 
        </div> 
    </div>

</body>

</html>
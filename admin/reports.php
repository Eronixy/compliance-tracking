<?php
session_start();
include(__DIR__ . '/../config/connect.php');

if ($_SESSION['role'] != 'admin') {
    exit("Access Denied");
}

/* =========================
   TREND ANALYSIS
========================= */

/* Violations per user */
$violationsTrend = mysqli_query($conn, "
SELECT DATE(created_at) as date, COUNT(*) as total
FROM violations
GROUP BY DATE(created_at)
ORDER BY date ASC
");

/* Total counts */
$totalUsers = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users"));
$totalViolations = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM violations"));
$totalIncidents = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM incident_reports"));

/* HIGH RISK USERS */
$highRiskUsers = mysqli_query($conn, "
SELECT users.username, users.id, users.department,
COUNT(violations.id) as violation_count
FROM users
LEFT JOIN violations ON violations.user_id = users.id
GROUP BY users.id
HAVING violation_count >= 3
ORDER BY violation_count DESC
LIMIT 5
");

/* DEPARTMENT PERFORMANCE */
$deptPerformance = mysqli_query($conn, "
SELECT users.department,
COUNT(violations.id) as violations,
COUNT(incident_reports.id) as incidents
FROM users
LEFT JOIN violations ON violations.user_id = users.id
LEFT JOIN incident_reports ON incident_reports.user_id = users.id
GROUP BY users.department
ORDER BY violations DESC
");

/* RISK DISTRIBUTION */
$lowRisk = mysqli_fetch_assoc(mysqli_query($conn, "
SELECT COUNT(*) as total FROM violations WHERE severity='Low'
"))['total'];

$medRisk = mysqli_fetch_assoc(mysqli_query($conn, "
SELECT COUNT(*) as total FROM violations WHERE severity='Medium'
"))['total'];

$highRisk = mysqli_fetch_assoc(mysqli_query($conn, "
SELECT COUNT(*) as total FROM violations WHERE severity='High'
"))['total'];
?>

<!DOCTYPE html>
<html>


<body>

    <div class="app-layout">

        <?php include('sidebar.php'); ?>

        <div class="main-wrapper-dashboard">

            <!-- TOP BAR -->
            <div class="top-bar">
                <h1>Reports & Analytics</h1>
            </div>

            <!-- CONTENT BODY -->
            <div class="content-body">

            <!-- PAGE HEADER -->
            <div class="page-header-analytics">
                <h2 class="page-title">Reports & Analytics</h2>
                <p class="page-subtitle">Executive intelligence dashboard for compliance decision making</p>
            </div>

            <!-- KPI CARDS GRID -->
            <div class="kpi-grid-analytics">
                <div class="kpi-card kpi-users">
                    <div class="kpi-accent kpi-accent-users"></div>
                    <div class="kpi-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                    <div class="kpi-label">TOTAL USERS</div>
                    <div class="kpi-value"><?= $totalUsers ?></div>
                </div>
                <div class="kpi-card kpi-violations">
                    <div class="kpi-accent kpi-accent-violations"></div>
                    <div class="kpi-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" fill="#ef4444" stroke="#ef4444" stroke-width="1.5"/></svg></div>
                    <div class="kpi-label">TOTAL VIOLATIONS</div>
                    <div class="kpi-value"><?= $totalViolations ?></div>
                </div>
                <div class="kpi-card kpi-incidents">
                    <div class="kpi-accent kpi-accent-incidents"></div>
                    <div class="kpi-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" fill="#fb923c" stroke="#fb923c" stroke-width="1.5"/></svg></div>
                    <div class="kpi-label">TOTAL INCIDENTS</div>
                    <div class="kpi-value"><?= $totalIncidents ?></div>
                </div>
            </div>

            <!-- RISK DISTRIBUTION & HIGH RISK EMPLOYEES -->
            <div class="analytics-grid">
                <!-- RISK DISTRIBUTION -->
                <div class="analytics-card">
                    <div class="card-header">
                        <h3>Risk Distribution</h3>
                        <button class="card-menu">⋯</button>
                    </div>
                    <div class="risk-distribution">
                        <div class="risk-item">
                            <span class="risk-label">Low Risk</span>
                            <div class="risk-bar-container">
                                <div class="risk-bar" style="width: <?= ($lowRisk / max($lowRisk, $medRisk, $highRisk, 1)) * 100 ?>%; background-color: #3b82f6;"></div>
                            </div>
                            <span class="risk-count"><?= $lowRisk ?></span>
                        </div>
                        <div class="risk-item">
                            <span class="risk-label">Medium Risk</span>
                            <div class="risk-bar-container">
                                <div class="risk-bar" style="width: <?= ($medRisk / max($lowRisk, $medRisk, $highRisk, 1)) * 100 ?>%; background-color: #fb923c;"></div>
                            </div>
                            <span class="risk-count"><?= $medRisk ?></span>
                        </div>
                        <div class="risk-item">
                            <span class="risk-label">High Risk</span>
                            <div class="risk-bar-container">
                                <div class="risk-bar" style="width: <?= ($highRisk / max($lowRisk, $medRisk, $highRisk, 1)) * 100 ?>%; background-color: #ef4444;"></div>
                            </div>
                            <span class="risk-count"><?= $highRisk ?></span>
                        </div>
                    </div>
                </div>

                <!-- HIGH RISK EMPLOYEES -->
                <div class="analytics-card">
                    <h3 class="card-header-simple">High Risk Employees</h3>
                    <div class="high-risk-list">
                        <?php while ($u = mysqli_fetch_assoc($highRiskUsers)) { 
                            $initials = strtoupper(substr($u['username'], 0, 1));
                        ?>
                            <div class="risk-employee-item">
                                <div class="employee-avatar" style="background-color: #f3e8ff; color: #7c3aed;">D</div>
                                <div class="employee-info">
                                    <div class="employee-name"><?= htmlspecialchars($u['username']) ?></div>
                                    <div class="employee-dept"><?= htmlspecialchars($u['department'] ?? 'N/A') ?></div>
                                </div>
                                <div class="employee-violations"><?= $u['violation_count'] ?> Violations</div>
                                <div class="badge-high-risk">HIGH RISK</div>
                            </div>
                        <?php } ?>
                    </div>
                    <button class="btn-view-all">View All Risk Profiles</button>
                </div>
            </div>

            <!-- DEPARTMENT PERFORMANCE -->
            <div class="analytics-card full-width">
                <h3 class="card-header-simple">Department Performance</h3>
                <div class="dept-table-wrapper">
                    <table class="dept-performance-table">
                        <thead>
                            <tr>
                                <th>DEPARTMENT</th>
                                <th>VIOLATIONS</th>
                                <th>INCIDENTS</th>
                                <th>PERFORMANCE</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($d = mysqli_fetch_assoc($deptPerformance)) { 
                            $score = $d['violations'] + $d['incidents'];
                            if ($score >= 10) {
                                $badge = 'POOR';
                                $badgeClass = 'badge-poor';
                            } elseif ($score >= 5) {
                                $badge = 'AVERAGE';
                                $badgeClass = 'badge-average';
                            } else {
                                $badge = 'GOOD';
                                $badgeClass = 'badge-good';
                            }
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($d['department'] ?? 'N/A') ?></td>
                                <td><?= $d['violations'] ?></td>
                                <td><?= $d['incidents'] ?></td>
                                <td><span class="perf-badge <?= $badgeClass ?>"><?= $badge ?></span></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            </div>
        </div>

    </div>

</body>

</html>
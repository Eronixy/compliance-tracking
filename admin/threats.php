<?php
session_start();
include(__DIR__ . '/../config/connect.php');

if ($_SESSION['role'] != 'admin') {
    exit("Access Denied");
}

/* 1. HIGH SEVERITY INCIDENTS */
$highIncidents = mysqli_query($conn, "
SELECT incident_reports.*, users.username
FROM incident_reports
LEFT JOIN users ON users.id = incident_reports.user_id
WHERE incident_reports.severity = 'High'
ORDER BY date_reported DESC
");

/* 2. REPEATED VIOLATIONS (3 OR MORE) */
$repeatViolators = mysqli_query($conn, "
SELECT users.id, users.username, COUNT(violations.id) as violation_count
FROM users
LEFT JOIN violations ON violations.user_id = users.id
GROUP BY users.id
HAVING violation_count >= 3
");

/* 3. LOCKED ACCOUNTS (from logs) */
$lockedAccounts = mysqli_query($conn, "
SELECT DISTINCT users.id, users.username
FROM users
INNER JOIN activity_logs ON activity_logs.user_id = users.id
WHERE activity_logs.action LIKE '%LOCKED%'
");
?>

<!DOCTYPE html>
<html>

<head>
    <?php include('../includes/header.php'); ?>
</head>

<body>

    <div class="app-layout">

        <?php include('sidebar.php'); ?>

        <div class="main-wrapper-dashboard">

            <!-- TOP BAR -->
            <div class="top-bar">
                <h1>Threat Alerts</h1>
            </div>

            <!-- CONTENT BODY -->
            <div class="content-body">

            <!-- PAGE HEADER -->
            <div class="threat-hero">
                <h2 class="threat-title">Threat Alerts</h2>
                <p class="threat-subtitle">Auto-generated security risks based on system behavior</p>
            </div>

            <!-- THREAT ALERT CARDS -->
            <div class="threat-cards-grid">
                <div class="threat-card high-severity">
                    <div class="threat-icon">
                        <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                            <path d="M16 2L2 28h28L16 2z" stroke="#ef4444" stroke-width="2" fill="none"/>
                            <circle cx="16" cy="20" r="1.5" fill="#ef4444"/>
                            <line x1="16" y1="11" x2="16" y2="17" stroke="#ef4444" stroke-width="1.5"/>
                        </svg>
                    </div>
                    <div class="threat-label">HIGH SEVERITY INCIDENTS</div>
                    <div class="threat-value"><?= mysqli_num_rows($highIncidents) ?></div>
                </div>

                <div class="threat-card repeat-violators">
                    <div class="threat-icon">
                        <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                            <path d="M12 8L20 8" stroke="#d97706" stroke-width="2" stroke-linecap="round"/>
                            <path d="M20 16L12 16" stroke="#d97706" stroke-width="2" stroke-linecap="round"/>
                            <path d="M8 4L14 10" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M18 14L24 20" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="threat-label">REPEAT VIOLATORS</div>
                    <div class="threat-value"><?= mysqli_num_rows($repeatViolators) ?></div>
                </div>

                <div class="threat-card locked-accounts">
                    <div class="threat-icon">
                        <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                            <rect x="6" y="14" width="20" height="14" rx="2" stroke="#3b82f6" stroke-width="2"/>
                            <path d="M10 14V8C10 5.24 12.24 3 15 3C17.76 3 20 5.24 20 8V14" stroke="#3b82f6" stroke-width="2" fill="none"/>
                        </svg>
                    </div>
                    <div class="threat-label">LOCKED ACCOUNTS</div>
                    <div class="threat-value"><?= mysqli_num_rows($lockedAccounts) ?></div>
                </div>
            </div>

            <!-- HIGH SEVERITY INCIDENTS TABLE -->
            <div class="threat-section-card">
                <h3 class="threat-section-title">High Severity Incidents</h3>
                <div class="threat-table-wrapper">
                    <table class="threat-table">
                        <thead>
                            <tr>
                                <th>USER</th>
                                <th>TITLE</th>
                                <th>SEVERITY</th>
                                <th>DATE</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                        $highIncidents = mysqli_query($conn, "
                            SELECT incident_reports.*, users.username
                            FROM incident_reports
                            LEFT JOIN users ON users.id = incident_reports.user_id
                            WHERE incident_reports.severity = 'High'
                            ORDER BY date_reported DESC
                        ");
                        while ($i = mysqli_fetch_assoc($highIncidents)) { 
                            $userInitials = strtoupper(substr($i['username'], 0, 1));
                        ?>
                            <tr>
                                <td>
                                    <div class="threat-user-cell">
                                        <div class="threat-user-avatar"><?= $userInitials ?></div>
                                        <span><?= htmlspecialchars($i['username']) ?></span>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($i['title']) ?></td>
                                <td><span class="severity-high-badge">● High</span></td>
                                <td><?= date('M d, Y', strtotime($i['date_reported'])) ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TWO-COLUMN LAYOUT -->
            <div class="threat-grid-2col">
                <!-- REPEAT VIOLATORS -->
                <div class="threat-section-card">
                    <h3 class="threat-section-title">Repeat Violators</h3>
                    <div class="threat-table-wrapper">
                        <table class="threat-table">
                            <thead>
                                <tr>
                                    <th>USER</th>
                                    <th>TOTAL VIOLATIONS</th>
                                    <th>RISK LEVEL</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php 
                            $repeatViolators = mysqli_query($conn, "
                                SELECT users.id, users.username, COUNT(violations.id) as violation_count
                                FROM users
                                LEFT JOIN violations ON violations.user_id = users.id
                                GROUP BY users.id
                                HAVING violation_count >= 3
                            ");
                            while ($v = mysqli_fetch_assoc($repeatViolators)) { 
                                $userInitials = strtoupper(substr($v['username'], 0, 1));
                            ?>
                                <tr>
                                    <td>
                                        <div class="threat-user-cell">
                                            <div class="threat-user-avatar"><?= $userInitials ?></div>
                                            <span><?= htmlspecialchars($v['username']) ?></span>
                                        </div>
                                    </td>
                                    <td><?= $v['violation_count'] ?></td>
                                    <td><span class="risk-high-badge">● High</span></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- LOCKED PROFILES -->
                <div class="threat-section-card">
                    <h3 class="threat-section-title">Locked Profiles</h3>
                    <div class="threat-table-wrapper">
                        <table class="threat-table">
                            <thead>
                                <tr>
                                    <th>USER</th>
                                    <th>STATUS</th>
                                    <th>ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php 
                            $lockedAccounts = mysqli_query($conn, "
                                SELECT DISTINCT users.id, users.username
                                FROM users
                                INNER JOIN activity_logs ON activity_logs.user_id = users.id
                                WHERE activity_logs.action LIKE '%LOCKED%'
                            ");
                            while ($l = mysqli_fetch_assoc($lockedAccounts)) { 
                                $userInitials = strtoupper(substr($l['username'], 0, 1));
                            ?>
                                <tr>
                                    <td>
                                        <div class="threat-user-cell">
                                            <div class="threat-user-avatar"><?= $userInitials ?></div>
                                            <span><?= htmlspecialchars($l['username']) ?></span>
                                        </div>
                                    </td>
                                    <td><span class="locked-status-badge">🔒 LOCKED/SECURITY FLAGGED</span></td>
                                    <td><button class="btn-review">Review</button></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

                    </div>
                </div>

            </div>

        </body>

        </html>

                            </table>

                        </div>

                    </div>
                </div>

</body>

</html>
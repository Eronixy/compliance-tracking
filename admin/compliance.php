<?php
session_start();
include(__DIR__ . '/../config/connect.php');

if ($_SESSION['role'] != 'admin') {
    exit("Access Denied");
}

/* MAIN DATA */
$data = mysqli_query($conn, "
SELECT compliance_records.*, users.username, users.department, policies.policy_name
FROM compliance_records
INNER JOIN users ON compliance_records.user_id = users.id
INNER JOIN policies ON compliance_records.policy_id = policies.id
");

/* RISK COUNTS */
$total = mysqli_num_rows($data);

/* RESET POINTER */
mysqli_data_seek($data, 0);

/* RISK ANALYTICS */
$nonCompliant = mysqli_query($conn, "
SELECT COUNT(*) as total FROM compliance_records
WHERE compliance_status = 'Non-Compliant'
");

$pending = mysqli_query($conn, "
SELECT COUNT(*) as total FROM compliance_records
WHERE compliance_status = 'Pending'
");

$compliant = mysqli_query($conn, "
SELECT COUNT(*) as total FROM compliance_records
WHERE compliance_status = 'Compliant'
");

/* DEPARTMENT RISK */
$deptRisk = mysqli_query($conn, "
SELECT users.department,
SUM(CASE WHEN compliance_status='Non-Compliant' THEN 3
         WHEN compliance_status='Pending' THEN 1
         ELSE 0 END) as risk_score
FROM compliance_records
INNER JOIN users ON users.id = compliance_records.user_id
GROUP BY users.department
ORDER BY risk_score DESC
");

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html>

<head>
</head>

<body>

    <div class="app-layout">

        <?php include('sidebar.php'); ?>

        <div class="main-wrapper-dashboard">

            <!-- TOP BAR -->
            <div class="top-bar">
                <h1>Compliance</h1>
            </div>

            <!-- CONTENT BODY -->
            <div class="content-body">

                <div class="page-header-row">
                    <div>
                        <h1 class="page-title">Compliance Risk Monitoring</h1>
                        <p class="page-subtitle">Real-time compliance intelligence and risk detection across departments and employees.</p>
                    </div>
                </div>

                <!-- SUMMARY CARDS -->
                <div class="dashboard-cards">

                    <div class="stat-card">
                        <h3>Compliant</h3>
                        <p class="stat-value"><?= mysqli_fetch_assoc($compliant)['total'] ?></p>
                    </div>

                    <div class="stat-card">
                        <h3>Pending</h3>
                        <p class="stat-value"><?= mysqli_fetch_assoc($pending)['total'] ?></p>
                    </div>

                    <div class="stat-card">
                        <h3>Non-Compliant</h3>
                        <p class="stat-value"><?= mysqli_fetch_assoc($nonCompliant)['total'] ?></p>
                    </div>

                </div>

                <!-- DEPARTMENT RISK -->
                <div class="section-card">

                    <h2>Department Risk Comparison</h2>

                    <div class="table-box">
                        <table class="custom-table compliance-table">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>Risk Score</th>
                                    <th>Risk Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($d = mysqli_fetch_assoc($deptRisk)) { ?>
                                    <tr>
                                        <td><?= htmlspecialchars($d['department'] ?? 'N/A') ?></td>
                                        <td class="center-cell"><?= $d['risk_score'] ?></td>
                                        <td>
                                            <?php
                                            if ($d['risk_score'] >= 10) {
                                                echo "<span class='risk-pill risk-high'>High Risk</span>";
                                            } elseif ($d['risk_score'] >= 5) {
                                                echo "<span class='risk-pill risk-medium'>Medium Risk</span>";
                                            } else {
                                                echo "<span class='risk-pill risk-low'>Low Risk</span>";
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                </div>

                <!-- MAIN TABLE -->
                <div class="section-card">

                    <h2>Employee Compliance Records</h2>

                    <div class="table-box">

                        <table class="custom-table compliance-table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Policy</th>
                                    <th>Status</th>
                                    <th>Risk</th>
                                    <th>Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                mysqli_data_seek($data, 0);
                                while ($r = mysqli_fetch_assoc($data)) { ?>
                                    <tr>
                                        <td><?= htmlspecialchars($r['username']) ?></td>
                                        <td><?= htmlspecialchars($r['department'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($r['policy_name']) ?></td>
                                        <td>
                                            <?php
                                            if ($r['compliance_status'] == 'Compliant') {
                                                echo "<span class='status-pill status-compliant'>Compliant</span>";
                                            } elseif ($r['compliance_status'] == 'Pending') {
                                                echo "<span class='status-pill status-pending'>Pending</span>";
                                            } else {
                                                echo "<span class='status-pill status-noncompliant'>Non-Compliant</span>";
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            if ($r['compliance_status'] == "Non-Compliant") {
                                                echo "<span class='risk-pill risk-high'>High</span>";
                                            } elseif ($r['compliance_status'] == "Pending") {
                                                echo "<span class='risk-pill risk-medium'>Medium</span>";
                                            } else {
                                                echo "<span class='risk-pill risk-low'>Low</span>";
                                            }
                                            ?>
                                        </td>
                                        <td><?= htmlspecialchars($r['updated_at']) ?></td>
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
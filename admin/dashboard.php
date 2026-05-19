<?php
session_start();
include(__DIR__ . '/../config/connect.php');

if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

/* COUNTS */
$users = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users"));
$policies = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM policies"));
$incidents = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM incident_reports"));
$violations = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM violations"));
$tasks = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tasks"));

/* COMPLIANCE STATUS */
$compliant = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM compliance_records WHERE compliance_status='Compliant'"));
$pending = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM compliance_records WHERE compliance_status='Pending'"));
$non = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM compliance_records WHERE compliance_status='Non-Compliant'"));

$total = $compliant + $pending + $non;
$rate = ($total > 0) ? round(($compliant / $total) * 100) : 0;

$currentPage = basename($_SERVER['PHP_SELF']);
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

            <!-- Header -->
            <div class="top-bar">
                <h1>Dashboard</h1>
            </div>

            <div class="content-body">

                <div class="hero-card">
                    <div class="hero-content">
                        <h2>Welcome, Admin</h2>
                        <p>Overview of system activity, compliance status, and operational metrics.</p>
                    </div>
                    <div class="chart-container">
                        <div class="chart-ring" style="background: conic-gradient(#10b981 <?= $rate ?>%, #E2ECF7 0);">
                            <div class="chart-inner">
                                <span class="chart-perc"><?= $rate ?>%</span>
                                <span class="chart-label">RATE</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dashboard Metrics -->
                <div class="dashboard-cards">
                    <div class="metric-card">
                        <img src="../shared/img/users.jpg" alt="Users icon" class="metric-icon">
                        <span>Users</span>
                        <p class="stat-value"><?= $users ?></p>
                    </div>
                    <div class="metric-card">
                        <img src="../shared/img/policies.jpg" alt="Policies icon" class="metric-icon">
                        <span>Policies</span>
                        <p class="stat-value"><?= $policies ?></p>
                    </div>
                    <div class="metric-card">
                        <img src="../shared/img/incidents.jpg" alt="Incidents icon" class="metric-icon">
                        <span>Incidents</span>
                        <p class="stat-value"><?= $incidents ?></p>
                    </div>
                    <div class="metric-card">
                        <img src="../shared/img/violations.jpg" alt="Violations icon" class="metric-icon">
                        <span>Violations</span>
                        <p class="stat-value"><?= $violations ?></p>
                    </div>
                    <div class="metric-card">
                        <img src="../shared/img/tasks.jpg" alt="Tasks icon" class="metric-icon">
                        <span>Tasks</span>
                        <p class="stat-value"><?= $tasks ?></p>
                    </div>
                </div>

                <div class="dashboard-chart-card">
                    <h2>Compliance Overview</h2>
                    <div class="chart-wrapper">
                        <canvas id="complianceChart"></canvas>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('complianceChart').getContext('2d');
        const complianceChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Compliant', 'Pending', 'Non-Compliant'],
                datasets: [{
                    data: [<?= $compliant ?>, <?= $pending ?>, <?= $non ?>],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 14,
                                weight: '600',
                                family: "'Plus Jakarta Sans', sans-serif"
                            }
                        }
                    }
                }
            }
        });
    </script>

</body>

</html>
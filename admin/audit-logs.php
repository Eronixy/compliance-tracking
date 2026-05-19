<?php
session_start();
include(__DIR__ . '/../config/connect.php');

if ($_SESSION['role'] != 'admin') {
    exit("Access Denied");
}

/* FILTERS */
$where = "WHERE 1=1";

if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where .= " AND (users.username LIKE '%$search%'
                OR activity_logs.action LIKE '%$search%'
                OR activity_logs.module LIKE '%$search%')";
}

if (!empty($_GET['severity'])) {
    $severity = mysqli_real_escape_string($conn, $_GET['severity']);
    $where .= " AND activity_logs.severity = '$severity'";
}

/* DATA */
$data = mysqli_query($conn, "
SELECT activity_logs.*, users.username
FROM activity_logs
INNER JOIN users ON users.id = activity_logs.user_id
$where
ORDER BY log_time DESC
");
?>

<!DOCTYPE html>
<html>
<?php include('../includes/header.php'); ?>

<body>

<div class="app-layout">

    <?php include('sidebar.php'); ?>

    <div class="main-wrapper-dashboard">

        <!-- TOP BAR -->
        <div class="top-bar">
            <h1>Audit Logs & Activity Tracking</h1>
        </div>

        <!-- CONTENT BODY -->
        <div class="content-body">

        <!-- FILTER PANEL -->
        <form method="GET" style="display:flex;gap:10px;margin-bottom:20px;">
            <input type="text" name="search" placeholder="Search user / action / module" style="flex:1;padding:10px;border:1px solid #d1d5db;border-radius:6px;">

            <select name="severity" style="padding:10px;border:1px solid #d1d5db;border-radius:6px;">
                <option value="">All Severity</option>
                <option value="info">Info</option>
                <option value="warning">Warning</option>
                <option value="critical">Critical</option>
            </select>

            <button type="submit" style="background:#000;color:#fff;padding:10px 20px;border:none;border-radius:6px;cursor:pointer;font-weight:600;">Filter</button>
        </form>

        <!-- TABLE -->
        <div class="table-box">
            <div class="table-container">

                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Module</th>
                            <th>Status</th>
                            <th>Severity</th>
                            <th>Task ID</th>
                            <th>IP Address</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php while ($l = mysqli_fetch_assoc($data)) { ?>

                        <tr>

                            <td><?= htmlspecialchars($l['username']) ?></td>

                            <td><?= htmlspecialchars($l['action']) ?></td>

                            <td><?= htmlspecialchars($l['module']) ?></td>

                            <td><?= htmlspecialchars($l['status']) ?></td>

                            <!-- SEVERITY HIGHLIGHT -->
                            <td>
                                <?php if ($l['severity'] == 'critical') { ?>
                                    <span style="color:#ef4444;font-weight:bold;">⚠ <?= htmlspecialchars($l['severity']) ?></span>
                                <?php } elseif ($l['severity'] == 'warning') { ?>
                                    <span style="color:#f59e0b;"><?= htmlspecialchars($l['severity']) ?></span>
                                <?php } else { ?>
                                    <span style="color:#10b981;"><?= htmlspecialchars($l['severity']) ?></span>
                                <?php } ?>
                            </td>

                            <td><?= htmlspecialchars($l['task_id']) ?></td>

                            <td><?= htmlspecialchars($l['ip_address']) ?></td>

                            <td><?= htmlspecialchars($l['log_time']) ?></td>

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
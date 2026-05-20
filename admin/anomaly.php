<?php
session_start();
include(__DIR__ . '/../config/connect.php');

if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

/* -------------------------
   DETECT ANOMALIES
   (multiple failed logins per IP/user)
--------------------------*/
$data = mysqli_query($conn, "
SELECT
    user_id,
    SUBSTRING_INDEX(action,'IP: ',-1) as ip,
    COUNT(*) as attempts
FROM activity_logs
WHERE action LIKE '%FAILED LOGIN%'
GROUP BY user_id, ip
HAVING attempts >= 3
ORDER BY attempts DESC
");
?>

<!DOCTYPE html>
<html>

<!DOCTYPE html>
<html>

<?php include('../includes/headeradmin.php'); ?>

<body>

    <div class="container dashboard">

        <?php include('sidebar.php'); ?>

        <div class="main-content glass">

            <h1>🧠 Anomaly Detection System</h1>

            <div class="glass" style="padding:20px; margin-top:20px;">

                <table>

                    <tr>
                        <th>User ID</th>
                        <th>IP Address</th>
                        <th>Failed Attempts</th>
                        <th>Status</th>
                    </tr>

                    <?php while ($row = mysqli_fetch_assoc($data)) { ?>

                        <tr>

                            <td><?= $row['user_id'] ?></td>
                            <td><?= $row['ip'] ?></td>
                            <td><?= $row['attempts'] ?></td>

                            <td>
                                <span style="color:#ff4d4d;font-weight:bold;">
                                    🚨 Suspicious Activity
                                </span>
                            </td>

                        </tr>

                    <?php } ?>

                </table>

            </div>

        </div>

    </div>

</body>

</html>
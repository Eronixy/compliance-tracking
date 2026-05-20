<?php
session_start();
include(__DIR__ . '/../config/connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'employee') {
    header("Location: ../index.php");
    exit();
}

$userID = $_SESSION['id'];
$name = $_SESSION['username'];

/* FETCH INCIDENTS */
$query = mysqli_query($conn,"
    SELECT * FROM incident_reports
    WHERE user_id='$userID'
    ORDER BY date_reported DESC
");

$totalReports = mysqli_num_rows($query);
?>

<!DOCTYPE html>
<html>

    <?php include('../includes/headeremployee.php'); ?>

<body>

<div class="container dashboard">

<?php include('sidebar.php'); ?>

<div class="main-content glass">

    <?php
    $currentSection = 'My Reports';
    ?>

    <div class="section-bar">
        <div class="section-name"><?= htmlspecialchars($currentSection) ?></div>
    </div>

    <h1>🚨 My Incident Reports</h1>

    <p style="opacity:0.7;">
        Track submitted incidents and investigation progress
    </p>

    <!-- SUMMARY -->
    <div class="cards" style="margin-top:20px;">

        <div class="card glass">
            <h3>Total Reports</h3>
            <p><?= $totalReports ?></p>
        </div>

        <div class="card glass">
            <h3>Quick Action</h3>

            <a href="submit-report.php" class="btn">
                + Submit New Report
            </a>
        </div>

    </div>

    <!-- INCIDENT TABLE -->
    <div class="glass" style="padding:20px;margin-top:20px;">

        <div class="table-container">

            <table border="1" width="100%" cellpadding="8">

                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Severity</th>
                    <th>Status</th>
                    <th>Proof</th>
                    <th>Date Reported</th>
                </tr>

                <?php while($row = mysqli_fetch_assoc($query)){ ?>

                <tr>

                    <!-- TITLE -->
                    <td>
                        <?= $row['title']; ?>
                    </td>

                    <!-- DESCRIPTION -->
                    <td style="max-width:250px;">
                        <?= nl2br($row['description']); ?>
                    </td>

                    <!-- SEVERITY -->
                    <td>

                        <?php
                        if ($row['severity'] == 'Critical') {
                            echo "<span style='color:red;font-weight:bold;'>🚨 Critical</span>";
                        }
                        elseif ($row['severity'] == 'High') {
                            echo "<span style='color:#ff4d4d;'>🔴 High</span>";
                        }
                        elseif ($row['severity'] == 'Medium') {
                            echo "<span style='color:orange;'>🟡 Medium</span>";
                        }
                        else {
                            echo "<span style='color:lightgreen;'>🟢 Low</span>";
                        }
                        ?>

                    </td>

                    <!-- STATUS -->
                    <td>

                        <?php
                        if ($row['status'] == 'Resolved') {
                            echo "<span style='color:lightgreen;'>🟢 Resolved</span>";
                        }
                        elseif ($row['status'] == 'Under Review') {
                            echo "<span style='color:orange;'>🟡 Under Review</span>";
                        }
                        elseif ($row['status'] == 'Escalated') {
                            echo "<span style='color:red;'>🚨 Escalated</span>";
                        }
                        else {
                            echo "<span style='color:#ccc;'>⚪ Pending</span>";
                        }
                        ?>

                    </td>

                    <!-- PROOF -->
                    <td>

                        <?php if (!empty($row['proof_image'])) { ?>

                            <a href="../uploads/<?= $row['proof_image']; ?>" target="_blank">
                                View Proof
                            </a>

                        <?php } else { ?>

                            No File

                        <?php } ?>

                    </td>

                    <!-- DATE -->
                    <td>
                        <?= $row['date_reported']; ?>
                    </td>

                </tr>

                <?php } ?>

            </table>

        </div>

    </div>

</div>
</div>

</body>
</html>
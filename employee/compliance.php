<?php
session_start();
include(__DIR__ . '/../config/connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'employee') {
    header("Location: ../index.php");
    exit();
}

$userID = $_SESSION['id'];
$name = $_SESSION['username'];

/* FETCH POLICIES + COMPLIANCE STATUS */
$query = mysqli_query($conn, "
    SELECT compliance_records.*, policies.policy_name, policies.description
    FROM compliance_records
    INNER JOIN policies ON compliance_records.policy_id = policies.id
    WHERE compliance_records.user_id='$userID'
    ORDER BY compliance_records.updated_at DESC
");

$total = mysqli_num_rows($query);
?>

<!DOCTYPE html>
<html>

    <?php include('../includes/headeremployee.php'); ?>

<body>

<div class="container dashboard">

<?php include('sidebar.php'); ?>

<div class="main-content glass">

    <?php
    $currentSection = 'Compliance';
    ?>

    <div class="section-bar">
        <div class="section-name"><?= htmlspecialchars($currentSection) ?></div>
    </div>

    <h1>📜 Follow Policies</h1>

    <p style="opacity:0.7;">
        Read-only compliance policies assigned to you
    </p>

    <!-- SUMMARY -->
    <div class="cards" style="margin-top:20px;">

        <div class="card glass">
            <h3>Total Policies</h3>
            <p><?= $total ?></p>
        </div>

        <div class="card glass">
            <h3>Status Guide</h3>
            <p style="font-size:14px;">
                🟢 Compliant<br>
                🟡 Pending<br>
                🔴 Non-Compliant
            </p>
        </div>

    </div>

    <!-- POLICY LIST -->
    <div class="glass" style="padding:20px;margin-top:20px;">

        <div class="table-container">

            <table border="1" width="100%" cellpadding="8">

                <tr>
                    <th>Policy</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Risk Level</th>
                    <th>Last Updated</th>
                </tr>

                <?php while ($row = mysqli_fetch_assoc($query)) { ?>

                <tr>

                    <!-- POLICY NAME -->
                    <td>
                        <b><?= $row['policy_name']; ?></b>
                    </td>

                    <!-- DESCRIPTION -->
                    <td style="max-width:300px;">
                        <?= nl2br($row['description']); ?>
                    </td>

                    <!-- STATUS -->
                    <td>
                        <?php
                        if ($row['compliance_status'] == "Compliant") {
                            echo "🟢 Compliant";
                        }
                        elseif ($row['compliance_status'] == "Pending") {
                            echo "🟡 Pending";
                        }
                        else {
                            echo "🔴 Non-Compliant";
                        }
                        ?>
                    </td>

                    <!-- RISK LEVEL -->
                    <td>
                        <?php
                        if ($row['compliance_status'] == "Non-Compliant") {
                            echo "<span style='color:red;font-weight:bold;'>High Risk</span>";
                        }
                        elseif ($row['compliance_status'] == "Pending") {
                            echo "<span style='color:orange;'>Medium Risk</span>";
                        }
                        else {
                            echo "<span style='color:lightgreen;'>Low Risk</span>";
                        }
                        ?>
                    </td>

                    <!-- LAST UPDATED -->
                    <td>
                        <?= $row['updated_at']; ?>
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
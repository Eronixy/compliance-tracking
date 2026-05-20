<?php
session_start();
include(__DIR__ . '/../config/connect.php');

if ($_SESSION['role'] != 'admin') {
    exit("Access Denied");
}

/* CREATE VIOLATION */
if (isset($_POST['create_violation'])) {

    $user_id = $_POST['user_id'];
    $violation_type = mysqli_real_escape_string($conn, $_POST['violation_type']);
    $severity = $_POST['severity'];

    mysqli_query($conn, "
        INSERT INTO violations (user_id, violation_type, severity, status)
        VALUES ('$user_id', '$violation_type', '$severity', 'Open')
    ");

    /* OPTIONAL: increase violation count if column exists */
    mysqli_query($conn, "
        UPDATE users
        SET violation_count = violation_count + 1
        WHERE id = '$user_id'
    ");
}

/* RESOLVE VIOLATION */
if (isset($_POST['resolve_violation'])) {

    $id = $_POST['violation_id'];

    mysqli_query($conn, "
        UPDATE violations
        SET status = 'Resolved'
        WHERE id = '$id'
    ");
}

/* FETCH VIOLATIONS */
$violations = mysqli_query($conn, "
SELECT violations.*, users.username
FROM violations
LEFT JOIN users ON users.id = violations.user_id
ORDER BY violations.id DESC
");

/* USERS LIST */
$users = mysqli_query($conn, "SELECT id, username FROM users");
?>

<!DOCTYPE html>
<html>



<body>

    <div class="app-layout">

        <?php include('sidebar.php'); ?>

        <div class="main-wrapper-dashboard">

            <!-- TOP BAR -->
            <div class="top-bar">
                <h1>Violations Management</h1>
            </div>

            <!-- CONTENT BODY -->
            <div class="content-body">

            <!-- CREATE VIOLATION FORM -->
            <div class="chart-container">

                <h2>Create Violation</h2>

                <form method="POST">

                    <select name="user_id" required>
                        <option value="">Select User</option>
                        <?php while ($u = mysqli_fetch_assoc($users)) { ?>
                            <option value="<?= $u['id'] ?>">
                                <?= $u['username'] ?>
                            </option>
                        <?php } ?>
                    </select>

                    <input type="text" name="violation_type" placeholder="Violation Type" required>

                    <select name="severity" required>
                        <option value="Low">Low</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                    </select>

                    <button type="submit" name="create_violation">
                        Create Violation
                    </button>

                </form>

            </div>

            <!-- TABLE -->
            <div class="chart-container">

                <h2>Violations List</h2>

                <div class="table-box">
                    <table class="custom-table">

                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Violation</th>
                                <th>Severity</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        <?php while ($v = mysqli_fetch_assoc($violations)) { ?>

                            <tr>

                                <td>
                                    <?= htmlspecialchars($v['username'] ?? 'Unknown') ?>
                                    <br>
                                    <small>EMP-<?= $v['user_id'] ?></small>
                                </td>

                                <td><?= htmlspecialchars($v['violation_type']) ?></td>

                                <td>
                                    <?php
                                    if ($v['severity'] == 'High') echo "<span style='color:#ef4444;font-weight:600;'>High</span>";
                                    elseif ($v['severity'] == 'Medium') echo "<span style='color:#f59e0b;font-weight:600;'>Medium</span>";
                                    else echo "<span style='color:#10b981;font-weight:600;'>Low</span>";
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    if ($v['status'] == 'Resolved') echo "<span style='color:#10b981;font-weight:600;'>Resolved</span>";
                                    else echo "<span style='color:#ef4444;font-weight:600;'>Open</span>";
                                    ?>
                                </td>

                                <td><?= htmlspecialchars($v['created_at']) ?></td>

                                <td>

                                    <?php if ($v['status'] != 'Resolved') { ?>

                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="violation_id" value="<?= $v['id'] ?>">
                                            <button type="submit" name="resolve_violation" style="background:#10b981;color:#fff;padding:6px 12px;border:none;border-radius:6px;cursor:pointer;font-size:12px;">
                                                Resolve
                                            </button>
                                        </form>

                                    <?php } else { ?>
                                        <span style="color:#10b981;">Done</span>
                                    <?php } ?>

                                </td>

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
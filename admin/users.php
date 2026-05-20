<?php
session_start();
include(__DIR__ . '/../config/connect.php');

if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

/* =========================
   ACTIONS
========================= */

// LOCK
if (isset($_GET['lock'])) {

    $id = $_GET['lock'];

    mysqli_query($conn, "
        UPDATE users
        SET is_locked=1
        WHERE id='$id'
    ");

    header("Location: users.php");
    exit();
}

// UNLOCK
if (isset($_GET['unlock'])) {

    $id = $_GET['unlock'];

    mysqli_query($conn, "
        UPDATE users
        SET is_locked=0,
            login_attempts=0
        WHERE id='$id'
    ");

    header("Location: users.php");
    exit();
}

// RESET PASSWORD
if (isset($_GET['reset'])) {

    $id = $_GET['reset'];

    $result = mysqli_query($conn, "SELECT role FROM users WHERE id='$id'");
    $user = mysqli_fetch_assoc($result);

    if ($user) {

        $defaultPassword = ($user['role'] == 'admin') ? "admin123" : "employee123";
        $hashed = password_hash($defaultPassword, PASSWORD_DEFAULT);

        mysqli_query($conn, "
            UPDATE users
            SET password='$hashed',
                must_change_password=1
            WHERE id='$id'
        ");
    }

    header("Location: users.php?success=passwordreset");
    exit();
}

// RESET VIOLATION COUNT
if (isset($_GET['reset_violation'])) {

    $id = $_GET['reset_violation'];

    mysqli_query($conn, "
        UPDATE users
        SET login_attempts=0,
            is_locked=0
        WHERE id='$id'
    ");

    header("Location: users.php?success=violationreset");
    exit();
}

// DELETE USER
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    // prevent self-delete
    if ($id == $_SESSION['id']) {
        header("Location: users.php?error=cannotdeleteown");
        exit();
    }

    mysqli_query($conn, "
        DELETE FROM users
        WHERE id='$id'
    ");

    header("Location: users.php?success=deleted");
    exit();
}

/* =========================
   USERS LIST
========================= */

$users = mysqli_query($conn, "
    SELECT *
    FROM users
    ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html>


<body>

<div class="app-layout">

<?php include('sidebar.php'); ?>

<div class="main-wrapper-dashboard">

<!-- TOP BAR -->
<div class="top-bar">
    <h1>User Management</h1>
</div>

<!-- CONTENT BODY -->
<div class="content-body">

<!-- HEADER WITH CREATE BUTTON -->
<div class="page-header-row">
    <div>
        <h2 class="page-title">User Management</h2>
        <p class="page-subtitle">Centralize your organizational governance. Manage employee access levels, security protocols, and administrative account permissions from one shared dashboard.</p>
    </div>

    <a href="create-user.php" class="btn-create-user">+ Create User</a>
</div>

<!-- SUCCESS / ERROR MESSAGES -->
<?php if (isset($_GET['success']) && $_GET['success'] == 'passwordreset') { ?>
    <div class="alert-banner alert-success">✔ Password reset successfully!</div>
<?php } ?>

<?php if (isset($_GET['success']) && $_GET['success'] == 'violationreset') { ?>
    <div class="alert-banner alert-success">✔ Violation count reset!</div>
<?php } ?>

<?php if (isset($_GET['success']) && $_GET['success'] == 'deleted') { ?>
    <div class="alert-banner alert-success">✔ User deleted successfully!</div>
<?php } ?>

<?php if (isset($_GET['error']) && $_GET['error'] == 'cannotdeleteown') { ?>
    <div class="alert-banner alert-error">❌ You cannot delete your own account!</div>
<?php } ?>

<!-- TABLE -->
<div class="user-table-card">
    <table class="custom-table">
    <thead>
        <tr>
            <th>Employee ID</th>
            <th>Username</th>
            <th>Department</th>
            <th>Role</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($users)) { ?>
        <tr>
            <td><?= htmlspecialchars($row['employee_id'] ?? 'NOT ASSIGNED') ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['department'] ?? 'N/A') ?></td>
            <td><?= strtoupper(htmlspecialchars($row['role'])) ?></td>

            <td>
                <?php if ($row['is_locked']) { ?>
                    <span class="status-badge status-locked">LOCKED</span>
                <?php } else { ?>
                    <span class="status-badge status-active">ACTIVE</span>
                <?php } ?>
            </td>

            <td class="actions-cell">
                <div class="table-actions">
                    <?php if ($row['is_locked'] == 0) { ?>
                        <a href="?lock=<?= $row['id'] ?>" class="action-link action-warning">Lock</a>
                    <?php } else { ?>
                        <a href="?unlock=<?= $row['id'] ?>" class="action-link action-success">Unlock</a>
                    <?php } ?>

                    <a href="?reset=<?= $row['id'] ?>" class="action-link action-danger">Reset PW</a>
                    <a href="?reset_violation=<?= $row['id'] ?>" class="action-link action-warning">Reset Violation</a>
                    <a href="edit-users.php?id=<?= $row['id'] ?>" class="action-link action-primary">Edit</a>
                    <a href="?delete=<?= $row['id'] ?>"
                       onclick="return confirm('Are you sure you want to delete this user? This cannot be undone!');"
                       class="action-link action-danger">Delete</a>
                </div>
            </td>

        </tr>

        <?php } ?>

    </tbody>
    </table>

</div>

</div>
</div>

</body>
</html>
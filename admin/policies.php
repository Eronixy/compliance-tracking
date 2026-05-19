<?php
session_start();
include(__DIR__ . '/../config/connect.php');

if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

/* =========================
   CREATE POLICY
========================= */
if (isset($_POST['create_policy'])) {

    $name = mysqli_real_escape_string($conn, $_POST['policy_name']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);

    mysqli_query($conn, "
        INSERT INTO policies (policy_name, description, status)
        VALUES ('$name', '$desc', 'Inactive')
    ");

    header("Location: policies.php?success=created");
    exit();
}

/* =========================
   TOGGLE POLICY STATUS
========================= */
if (isset($_GET['toggle'])) {

    $id = intval($_GET['toggle']);

    $res = mysqli_query($conn, "SELECT status FROM policies WHERE id=$id");

    if ($res && mysqli_num_rows($res) > 0) {

        $row = mysqli_fetch_assoc($res);

        $newStatus = ($row['status'] === 'Active') ? 'Inactive' : 'Active';

        mysqli_query($conn, "
            UPDATE policies
            SET status='$newStatus'
            WHERE id=$id
        ");
    }

    header("Location: policies.php");
    exit();
}

/* =========================
   ASSIGN POLICY TO USER
========================= */
if (isset($_POST['assign_policy'])) {

    $user_id = intval($_POST['user_id']);
    $policy_id = intval($_POST['policy_id']);

    // prevent duplicate assignment
    $check = mysqli_query($conn, "
        SELECT id FROM user_policies
        WHERE user_id=$user_id AND policy_id=$policy_id
    ");

    if (mysqli_num_rows($check) == 0) {

        mysqli_query($conn, "
            INSERT INTO user_policies (user_id, policy_id)
            VALUES ($user_id, $policy_id)
        ");
    }

    header("Location: policies.php?success=assigned");
    exit();
}

/* =========================
   DATA FETCH
========================= */
$policies = mysqli_query($conn, "SELECT * FROM policies ORDER BY id DESC");
$users = mysqli_query($conn, "SELECT id, username FROM users");
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
    <h1>Policies</h1>
</div>

<!-- CONTENT BODY -->
<div class="content-body">

    <div class="page-header-row">
        <div>
            <h1 class="page-title">Policy Management</h1>
            <p class="page-subtitle">Create, activate, and assign compliance policies.</p>
        </div>
    </div>

    <?php if (isset($_GET['success']) && $_GET['success'] == 'created') { ?>
        <div class="alert-banner alert-success">✔ Policy created successfully!</div>
    <?php } ?>

    <?php if (isset($_GET['success']) && $_GET['success'] == 'assigned') { ?>
        <div class="alert-banner alert-success">✔ Policy assigned successfully!</div>
    <?php } ?>

    <div class="policy-grid">

        <div class="policy-card">
            <div class="policy-card-header">
                <div class="policy-card-icon"><img src="../shared/img/createnewpolicy.jpg" alt="Create Policy"></div>
                <div>
                    <h3>Create New Policy</h3>
                </div>
            </div>

            <form method="POST" class="policy-form">
                <label class="form-label">Policy Name</label>
                <input type="text" name="policy_name" placeholder="e.g. Data Privacy Act 2024" required class="form-input">

                <label class="form-label">Description</label>
                <textarea name="description" placeholder="Briefly describe the purpose and scope of this policy..." required class="form-textarea"></textarea>

                <button type="submit" name="create_policy" class="btn-primary">+ Create Policy</button>
            </form>
        </div>

        <div class="policy-card">
            <div class="policy-card-header">
                <div class="policy-card-icon"><img src="../shared/img/assignpolicy.jpg" alt="Assign Policy"></div>
                <div>
                    <h3>Assign Policy</h3>
                </div>
            </div>

            <form method="POST" class="policy-form">
                <label class="form-label">Select Employee</label>
                <select name="user_id" required class="form-input">
                    <option value="">Select Employee</option>
                    <?php while ($u = mysqli_fetch_assoc($users)) { ?>
                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                    <?php } ?>
                </select>

                <label class="form-label">Select Policy</label>
                <select name="policy_id" required class="form-input">
                    <option value="">Select Policy</option>
                    <?php
                    $plist = mysqli_query($conn, "SELECT * FROM policies");
                    while ($p = mysqli_fetch_assoc($plist)) {
                    ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['policy_name']) ?> (<?= htmlspecialchars($p['status']) ?>)</option>
                    <?php } ?>
                </select>

                <button type="submit" name="assign_policy" class="btn-secondary">+ Assign Policy</button>
            </form>
        </div>

    </div>

    <div class="section-card">
        <h2>Active Policies Overview</h2>
        <div class="table-box">
            <table class="custom-table policy-table">
                <thead>
                    <tr>
                        <th>Policy Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($p = mysqli_fetch_assoc($policies)) { ?>
                        <tr>
                            <td><?= htmlspecialchars($p['policy_name']) ?></td>
                            <td><?= htmlspecialchars($p['description']) ?></td>
                            <td>
                                <?php if ($p['status'] === 'Active') { ?>
                                    <span class="status-pill status-compliant">Active</span>
                                <?php } else { ?>
                                    <span class="status-pill status-pending">Inactive</span>
                                <?php } ?>
                            </td>
                            <td>
                                <a href="policies.php?toggle=<?= $p['id'] ?>" onclick="return confirm('Toggle this policy status?');" class="action-link action-primary">Toggle</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</div>

</body>
</html>
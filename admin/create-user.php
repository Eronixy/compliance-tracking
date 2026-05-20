<?php
session_start();
include(__DIR__ . '/../config/connect.php');

if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$message = "";

/* =========================
   CREATE USER (NO HASH)
========================= */
if (isset($_POST['create'])) {

    $username = isset($_POST['username']) ? mysqli_real_escape_string($conn, $_POST['username']) : '';
    $email = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : '';
    $contact_number = isset($_POST['contact_number']) ? mysqli_real_escape_string($conn, $_POST['contact_number']) : '';
    $department = isset($_POST['department']) ? mysqli_real_escape_string($conn, $_POST['department']) : '';
    $role = isset($_POST['role']) ? mysqli_real_escape_string($conn, $_POST['role']) : '';

    // ✅ DEFAULT PASSWORD (NO INPUT NEEDED)
    $temp_password = "password123";

    // INSERT USER
    mysqli_query($conn, "
        INSERT INTO users (
            username,
            email,
            contact_number,
            password,
            role,
            department,
            is_locked,
            login_attempts,
            must_change_password
        )
        VALUES (
            '$username',
            '$email',
            '$contact_number',
            '$temp_password',
            '$role',
            '$department',
            0,
            0,
            1
        )
    ");

    $last_id = mysqli_insert_id($conn);

    $employee_id = strtoupper($department) . "-EMP-" . str_pad($last_id, 5, "0", STR_PAD_LEFT);

    mysqli_query($conn, "
        UPDATE users
        SET employee_id='$employee_id'
        WHERE id='$last_id'
    ");

    $message = "✔ User created successfully! Default password: password123 | Employee ID: $employee_id";
}
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
                <h1>Create Employee Account</h1>
            </div>

            <!-- CONTENT BODY -->
            <div class="content-body">

            <p style="opacity:0.7;margin-bottom:20px;">HR onboarding system</p>

            <!-- SUCCESS MESSAGE -->
            <?php if ($message) { ?>
                <div style="background:#d1fae5;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:20px;border-left:4px solid #047857;">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php } ?>

            <!-- FORM -->
            <div class="chart-container">
                <h2>New User Form</h2>

                <form method="POST" style="max-width:500px;">

                    <div class="input-group">
                        <label>Full Name</label>
                        <input type="text" name="username" placeholder="Full Name" required style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:8px;">
                    </div>

                    <div class="input-group">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="Email Address" required style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:8px;">
                    </div>

                    <div class="input-group">
                        <label>Contact Number</label>
                        <input type="text" name="contact_number" placeholder="Contact Number" required style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:8px;">
                    </div>

                    <div class="input-group">
                        <label>Department</label>
                        <select name="department" required style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:8px;">
                            <option value="">Select Department</option>
                            <option value="IT">IT</option>
                            <option value="HR">HR</option>
                            <option value="SEC">Security</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Role</label>
                        <select name="role" required style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:8px;">
                            <option value="">Select Role</option>
                            <option value="employee">Employee</option>
                            <option value="security">Security</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <button type="submit" name="create"
                        style="width:100%;background:#000;color:#fff;padding:12px;border:none;border-radius:8px;font-weight:600;cursor:pointer;transition:0.2s;">
                        Create User
                    </button>

                </form>

            </div>

            </div>
        </div>
    </div>

</body>

</html>
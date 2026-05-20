<?php
session_start();
include(__DIR__ . '/../config/connect.php');

if ($_SESSION['role'] != 'employee') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['id'];

$query = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($query);

if (isset($_POST['update_account'])) {

    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'];
    $password = $_POST['password'];

    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "
            UPDATE users
            SET email='$email',
                contact_number='$contact_number',
                password='$hashedPassword'
            WHERE id='$user_id'
        ";
    } else {
        $sql = "
            UPDATE users
            SET email='$email',
                contact_number='$contact_number'
            WHERE id='$user_id'
        ";
    }

    mysqli_query($conn, $sql);

    header("Location: edit_account.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html>

    <?php include('../includes/headeremployee.php'); ?>

<body>

<div class="container dashboard">

    <?php include('sidebar.php'); ?>

    <div class="main-content glass">

        <?php
        $currentSection = 'My Profile';
        ?>

        <div class="section-bar">
            <div class="section-name"><?= htmlspecialchars($currentSection) ?></div>
        </div>

        <h1>👤 My Profile</h1>

        <p style="opacity:0.7; margin-bottom:20px;">
            Manage your account information and security settings.
        </p>

        <?php if (isset($_GET['success'])) { ?>
            <div class="success-box">
                ✔ Account updated successfully!
            </div>
        <?php } ?>

        <!-- PROFILE CARD -->
        <div class="profile-card glass">

            <div class="avatar">
                👤
            </div>

            <form method="POST">

                <!-- EMPLOYEE ID (READ ONLY) -->
                <div class="input-group">
                    <label>Employee ID</label>
                    <input type="text" value="<?= $user['employee_id'] ?>" readonly>
                </div>

                <!-- FULL NAME (READ ONLY) -->
                <div class="input-group">
                    <label>Full Name</label>
                    <input type="text" value="<?= $user['username'] ?>" readonly>
                </div>

                <!-- EMAIL -->
                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= $user['email'] ?>" required>
                </div>

                <!-- CONTACT NUMBER -->
                <div class="input-group">
                    <label>Contact Number</label>
                    <input type="text" name="contact_number" value="<?= $user['contact_number'] ?>" required>
                </div>

                <!-- PASSWORD -->
                <div class="input-group">
                    <label>New Password</label>
                    <input type="password" name="password" placeholder="Enter new password">
                </div>

                <button class="btn full" type="submit" name="update_account">
                    Save Changes
                </button>

            </form>

        </div>

    </div>
</div>

</body>
</html>
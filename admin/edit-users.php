<?php
session_start();
include(__DIR__ . '/../config/connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {

    header("Location: ../index.php");
    exit();

}

if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$id = $_GET['id'];

$userQuery = mysqli_query($conn, "SELECT * FROM users WHERE id='$id'");
$user = mysqli_fetch_assoc($userQuery);

if (!$user) {
    die("User not found.");
}

/* UPDATE USER */
if (isset($_POST['update_user'])) {

    $employee_id = $_POST['employee_id'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'];
    $department = $_POST['department'];
    $role = $_POST['role'];

    mysqli_query($conn, "
        UPDATE users
        SET
            employee_id='$employee_id',
            username='$fullname',
            email='$email',
            contact_number='$contact_number',
            department='$department',
            role='$role'
        WHERE id='$id'
    ");

    header("Location: users.php?updated=1");
    exit();
}
?>

<!DOCTYPE html>
<html>


<body>

<div class="container dashboard">

    <?php include('sidebar.php'); ?>

    <div class="main-content glass">

        <h1>✏ Edit User</h1>

        <p style="opacity:0.7; margin-bottom:20px;">
            Update user information
        </p>

        <div class="glass" style="padding:30px; max-width:700px;">

            <form method="POST">

                <!-- EMPLOYEE ID -->
                <div class="input-group">
                    <label>Employee ID</label>
                    <input type="text"
                           name="employee_id"
                           value="<?= $user['employee_id'] ?>"
                           required>
                </div>

                <!-- FULL NAME -->
                <div class="input-group">
                    <label>Full Name</label>
                    <input type="text"
                           name="fullname"
                           value="<?= $user['username'] ?>"
                           required>
                </div>

                <!-- EMAIL -->
                <div class="input-group">
                    <label>Email</label>
                    <input type="email"
                           name="email"
                           value="<?= $user['email'] ?>"
                           required>
                </div>

                <!-- CONTACT NUMBER -->
                <div class="input-group">
                    <label>Contact Number</label>
                    <input type="text"
                           name="contact_number"
                           value="<?= $user['contact_number'] ?>"
                           required>
                </div>

                <!-- DEPARTMENT -->
                <div class="input-group">
                    <label>Department</label>
                    <input type="text"
                           name="department"
                           value="<?= $user['department'] ?>">
                </div>

                <!-- ROLE -->
                <div class="input-group">
                    <label>Role</label>

                    <select name="role" required>

                        <option value="employee"
                            <?= $user['role'] == 'employee' ? 'selected' : '' ?>>
                            Employee
                        </option>

                        <option value="security"
                            <?= $user['role'] == 'security' ? 'selected' : '' ?>>
                            Security
                        </option>

                        <option value="admin"
                            <?= $user['role'] == 'admin' ? 'selected' : '' ?>>
                            Admin
                        </option>

                    </select>

                </div>

                <button class="btn full"
                        type="submit"
                        name="update_user">

                    Save Changes

                </button>

            </form>

        </div>

    </div>

</div>

</body>
</html>
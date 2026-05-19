<?php
include('config/connect.php');
session_start();

$error = "";

if (isset($_POST['btnLogin'])) {

// FIX: use identifier instead of email
$identifier = mysqli_real_escape_string($conn, $_POST['identifier']);
$password = $_POST['password'];

$loginQuery = "
       SELECT * FROM users
       WHERE email = '$identifier'
       OR username = '$identifier'
       OR employee_id = '$identifier'
       LIMIT 1
   ";

$loginResult = mysqli_query($conn, $loginQuery);

if ($loginResult && mysqli_num_rows($loginResult) > 0) {

$user = mysqli_fetch_assoc($loginResult);

        // 🔒 CHECK IF LOCKED FIRST
        if ($user['is_locked'] == 1) {
            header("Location: index.php?error=locked");
            exit();
        }

// FIX: handle plain text password in DB (no hash mismatch issues)
if ($user['password'] === $password) {

$_SESSION['id'] = $user['id'];
$_SESSION['username'] = $user['username']; // your DB uses "username"
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];

if ($user['role'] == "admin") {
header("Location: __DIR__ . '/../admin/dashboard.php");
exit();
} elseif ($user['role'] == "security") {
header("Location: __DIR__ . '/../security/dashboard.php");
exit();
} elseif ($user['role'] == "employee") {
header("Location: __DIR__ . '/../employee/dashboard.php");
exit();
}
} else {
$error = "Invalid password";
}
} else {
$error = "User not found";
}
}
?>

<!DOCTYPE html>
<html>

<head>
    <?php include('includes/header.php'); ?>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            min-height: 100vh;
            width: 100%;
            background-color: #F7F8F9;
            background-image:
                radial-gradient(circle at 5% 20%, rgba(234, 230, 223, 50) 20%, transparent 30%),
                radial-gradient(circle at 85% 85%, rgba(226, 235, 246, 50) 20%, transparent 30%);
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow-x: hidden;
            color: #000;
        }

        .top-bar {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 70px;
            background: #FAF9F6;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            padding: 0 40px;
            z-index: 10;
            border-bottom: 1px solid #c4c7c7;

        }

        .top-bar-title {
            font-size: 22px;
            font-weight: 800;
            color: #000;
        }

        .container {
            width: 100%;
            min-height: 100vh;
            display: flex;
        }

        .login-card {
            width: 100%;
            max-width: 700px;
            padding: 60px 50px;
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: 4px 4px 0px 0px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: -40px;
            left: -40px;
            width: 130px;
            height: 130px;
            background-color: #EBE8E3;
            border-radius: 50%;
            z-index: 0;
        }

        .login-card::after {
            content: '';
            position: absolute;
            bottom: -60px;
            right: -40px;
            width: 250px;
            height: 250px;
            background-color: #E2ECF7;
            border-radius: 50%;
            z-index: 0;
        }

        .login-card-content {
            position: relative;
            z-index: 1;
        }

        .login-card h1 {
            text-align: center;
            margin-bottom: 8px;
            font-size: 32px;
            font-weight: 700;
        }

        .login-subtitle {
            text-align: center;
            font-size: 15px;
            color: #555;
            margin-bottom: 40px;
        }

        .input-group {
            margin-bottom: 24px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #333;
            font-weight: 400;
        }

        .input-group input {
            width: 100%;
            padding: 16px 20px;
            border: 1px solid #d1d5db;
            border-radius: 25px;
            outline: none;
            font-size: 14px;
            color: #333;
            background: #ffffff;
            transition: 0.3s;
        }

        .input-group input::placeholder {
            color: #a3a3a3;
        }

        .input-group input:focus {
            border-color: #999;
        }

        .btn {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 25px;
            background: #000;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        .btn:hover {
            transform: translateY(-3px);
            background: #222;
        }
    </style>
</head>

<body>

    <div class="top-bar">
        <span class="top-bar-title">Name</span>
    </div>

    <div class="login-card">

        <div class="login-card-content">

            <h1>Login</h1>

            <p class="login-subtitle">
                Use your company-issued credentials to log in
            </p>

            <?php if (!empty($error)): ?>
                <p style="color:#ff4d4d;text-align:center;margin-bottom:15px;font-size:14px;font-weight:600;">
                    <?= $error ?>
                </p>
            <?php endif; ?>

            <?php if (isset($_GET['error']) && $_GET['error'] == 'locked') { ?>

                <div id="popup" class="popup">
                    🚫 Your account has been locked. Please contact the administrator.
                </div>

                <script>
                    setTimeout(() => {
                        const popup = document.getElementById("popup");
                        if (popup) {
                            popup.style.opacity = "0";
                            setTimeout(() => popup.remove(), 500);
                        }
                    }, 5000);
                </script>

            <?php } ?>

            <form method="POST">

                <div class="input-group">
                    <label>Email</label>
                    <input type="text" name="identifier" placeholder="Enter your email" required>
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter your password (temporary or assigned)"
                        required>
                </div>

                <button class="btn" type="submit" name="btnLogin">
                    Login
                </button>

            </form>

        </div>
    </div>

</body>

</html>
<?php
session_start();
include(__DIR__ . '/../config/connect.php');

if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$tasks = mysqli_query($conn, "
    SELECT tasks.*, users.fullname
    FROM tasks
    INNER JOIN users
    ON tasks.assigned_to = users.id
");
?>


<body>

    <div class="app-layout">

        <?php include('sidebar.php'); ?>

        <div class="main-wrapper-dashboard">

            <!-- TOP BAR -->
            <div class="top-bar">
                <h1>Task Management</h1>
            </div>

            <!-- CONTENT BODY -->
            <div class="content-body">

            <div class="chart-container">

                <h2>Tasks</h2>

                <div class="table-box">

                    <table class="custom-table">

                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Assigned To</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Deadline</th>
                            </tr>
                        </thead>
                        <tbody>

                        <?php while ($t = mysqli_fetch_assoc($tasks)) { ?>

                            <tr>

                                <td><?php echo htmlspecialchars($t['title']); ?></td>

                                <td><?php echo htmlspecialchars($t['fullname']); ?></td>

                                <td><?php echo htmlspecialchars($t['priority']); ?></td>

                                <td><?php echo htmlspecialchars($t['status']); ?></td>

                                <td><?php echo htmlspecialchars($t['deadline']); ?></td>

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
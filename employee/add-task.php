<?php
session_start();
include(__DIR__ . '/../config/connect.php');

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'employee') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];

$editMode = false;
$task = null;
$task_id = null;

/* =========================
   CHECK IF EDIT MODE
========================= */
if (isset($_GET['id'])) {
    $editMode = true;
    $task_id = $_GET['id'];

    $result = mysqli_query($conn, "SELECT * FROM tasks WHERE id='$task_id'");
    $task = mysqli_fetch_assoc($result);

    if (!$task) {
        header("Location: tasks.php");
        exit();
    }
}

/* =========================
   ADD TASK
========================= */
if (isset($_POST['save_task']) && !$editMode) {

    $title = $_POST['title'];
    $priority = $_POST['priority'];
    $deadline = $_POST['deadline'];
    $department = $_POST['department'];

    mysqli_query($conn, "
        INSERT INTO tasks (title, priority, deadline, department, assigned_to)
        VALUES ('$title','$priority','$deadline','$department','$user_id')
    ");

    $new_id = mysqli_insert_id($conn);

    mysqli_query($conn, "
        INSERT INTO activity_logs (user_id, action, task_id, log_time)
        VALUES ('$user_id','Created task','$new_id',NOW())
    ");

    header("Location: tasks.php");
    exit();
}

/* =========================
   UPDATE TASK (EDIT MODE)
========================= */
if (isset($_POST['save_task']) && $editMode) {

    $title = $_POST['title'];
    $priority = $_POST['priority'];
    $deadline = $_POST['deadline'];
    $department = $_POST['department'];

    mysqli_query($conn, "
        UPDATE tasks
        SET title='$title',
            priority='$priority',
            deadline='$deadline',
            department='$department'
        WHERE id='$task_id'
    ");

    mysqli_query($conn, "
        INSERT INTO activity_logs (user_id, action, task_id, log_time)
        VALUES ('$user_id','Updated task (Task ID: $task_id)','$task_id',NOW())
    ");

    header("Location: tasks.php?updated=1");
    exit();
}
?>


<!DOCTYPE html>
<html>
<?php include('../includes/headeremployee.php'); ?>

<body>

    <div class="container dashboard">

        <?php include(__DIR__ . '/../includes/employee-sidebar.php'); ?>

        <div class="main-content glass">

            <h1>
                <?= $editMode ? "✏️ Edit Task" : "➕ Create Task" ?>
            </h1>

            <p style="opacity:0.7; margin-bottom:20px;">
                <?= $editMode ? "Update task details below." : "Fill out the form to create a new task." ?>
            </p>

            <div class="form-card glass">

                <form method="POST">

                    <!-- TITLE -->
                    <div class="input-group">
                        <label>Task Title</label>
                        <input type="text" name="title" value="<?= $editMode ? $task['title'] : '' ?>" required>
                    </div>

                    <!-- PRIORITY -->
                    <div class="input-group">
                        <label>Priority</label>
                        <select name="priority" required>
                            <option value="Low" <?= $editMode && $task['priority'] == "Low" ? "selected" : "" ?>>Low</option>
                            <option value="Medium" <?= $editMode && $task['priority'] == "Medium" ? "selected" : "" ?>>Medium
                            </option>
                            <option value="High" <?= $editMode && $task['priority'] == "High" ? "selected" : "" ?>>High
                            </option>
                        </select>
                    </div>

                    <!-- DEADLINE -->
                    <div class="input-group">
                        <label>Deadline</label>
                        <input type="date" name="deadline" value="<?= $editMode ? $task['deadline'] : '' ?>" required>
                    </div>

                    <!-- DEPARTMENT -->
                    <div class="input-group">
                        <label>Department</label>
                        <input type="text" name="department" value="<?= $editMode ? $task['department'] : '' ?>"
                            required>
                    </div>

                    <!-- SUBMIT -->
                    <button class="btn full" type="submit" name="save_task">
                        <?= $editMode ? "Update Task" : "Save Task" ?>
                    </button>

                </form>

            </div>

        </div>

    </div>

</body>

</html>
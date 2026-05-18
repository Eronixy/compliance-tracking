<?php
session_start();
include(__DIR__ . '/../config/connect.php');

if ($_SESSION['role'] != 'employee') {
    exit("Access Denied");
}

$user_id = $_SESSION['id'];

/* --- ADD NEW TASK --- */
if (isset($_POST['add_task'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $priority = mysqli_real_escape_string($conn, $_POST['priority']);
    $deadline = mysqli_real_escape_string($conn, $_POST['deadline']);
    $status = 'Not Started'; // Default status for new tasks

    // Insert into tasks table
    mysqli_query($conn, "
        INSERT INTO tasks (title, priority, deadline, status, assigned_to, created_at)
        VALUES ('$title', '$priority', '$deadline', '$status', '$user_id', NOW())
    ");

    $new_task_id = mysqli_insert_id($conn);

    // Log the activity
    mysqli_query($conn, "
        INSERT INTO activity_logs (user_id, action, task_id)
        VALUES ('$user_id', 'Created a new task: $title', '$new_task_id')
    ");

    // Refresh page to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

/* --- UPDATE TASK STATUS --- */
if (isset($_POST['update_task'])) {

    $task_id = mysqli_real_escape_string($conn, $_POST['task_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    mysqli_query($conn, "
        UPDATE tasks
        SET status='$status'
        WHERE id='$task_id' AND assigned_to='$user_id'
    ");

    mysqli_query($conn, "
        INSERT INTO activity_logs (user_id, action, task_id)
        VALUES ('$user_id', 'Updated task status to $status', '$task_id')
    ");

    // Refresh page
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

/* --- EDIT TASK --- */
if (isset($_POST['edit_task'])) {
    $task_id = mysqli_real_escape_string($conn, $_POST['task_id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $priority = mysqli_real_escape_string($conn, $_POST['priority']);
    $deadline = mysqli_real_escape_string($conn, $_POST['deadline']);

    mysqli_query($conn, "
        UPDATE tasks
        SET title='$title', priority='$priority', deadline='$deadline'
        WHERE id='$task_id' AND assigned_to='$user_id'
    ");

    mysqli_query($conn, "
        INSERT INTO activity_logs (user_id, action, task_id)
        VALUES ('$user_id', 'Edited task details for: $title', '$task_id')
    ");

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

/* --- DELETE TASK --- */
if (isset($_POST['delete_task'])) {
    $task_id = mysqli_real_escape_string($conn, $_POST['task_id']);

    mysqli_query($conn, "
        DELETE FROM tasks
        WHERE id='$task_id' AND assigned_to='$user_id'
    ");

    mysqli_query($conn, "
        INSERT INTO activity_logs (user_id, action, task_id)
        VALUES ('$user_id', 'Deleted task ID: $task_id', '$task_id')
    ");

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

/* --- GET ASSIGNED TASKS AND SORT INTO ARRAYS --- */
$tasks_query = mysqli_query($conn, "
    SELECT *
    FROM tasks
    WHERE assigned_to='$user_id'
    ORDER BY created_at DESC
");

$tasks_not_started = [];
$tasks_in_progress = [];
$tasks_completed = [];

while ($t = mysqli_fetch_assoc($tasks_query)) {
    if ($t['status'] == 'Not Started') {
        $tasks_not_started[] = $t;
    } elseif ($t['status'] == 'In Progress') {
        $tasks_in_progress[] = $t;
    } elseif ($t['status'] == 'Completed') {
        $tasks_completed[] = $t;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <?php include('../includes/header.php'); ?>
</head>

<body>

    <div class="app-layout">

        <?php include('sidebar.php'); ?>

        <div class="main-wrapper-tasks">

            <div class="top-bar">
                <h1>Task Board</h1>
            </div>

            <!-- Scrollable Content Area -->
            <div class="content-body">

                <div class="page-header-row">
                    <div>
                        <h1 class="page-title">Task Board</h1>
                        <p class="page-subtitle">Manage and track compliance objectives across departments.</p>
                    </div>
                    <button class="btn-add-task" onclick="openTaskModal()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3"
                            stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Add Task
                    </button>
                </div>

                <div class="kanban-board">

                    <!--Not StartedD -->
                    <div class="kanban-col">
                        <div class="col-header">
                            <div class="col-title"><span class="dot yellow"></span> Not Started</div>
                            <div class="col-count"><?= count($tasks_not_started) ?></div>
                        </div>
                        <div class="col-body">
                            <?php foreach ($tasks_not_started as $t): ?>
                                <div class="task-card">
                                    <div class="task-dept">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z">
                                            </path>
                                        </svg>
                                        GENERAL
                                    </div>
                                    <div class="task-badges">
                                        <span class="badge-priority <?= strtolower($t['priority']) ?>"><?= $t['priority'] ?>
                                            Priority</span>
                                        <span class="badge-status">Pending</span>
                                    </div>
                                    <div class="task-title"><?= htmlspecialchars($t['title']) ?></div>
                                    <div class="task-date">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="16" y1="2" x2="16" y2="6"></line>
                                            <line x1="8" y1="2" x2="8" y2="6"></line>
                                            <line x1="3" y1="10" x2="21" y2="10"></line>
                                        </svg>
                                        Due: <?= htmlspecialchars($t['deadline']) ?>
                                    </div>

                                    <div class="task-actions">
                                        <form method="POST" style="display:flex; width:100%; gap:10px;">
                                            <input type="hidden" name="task_id" value="<?= $t['id'] ?>">
                                            <input type="hidden" name="status" value="In Progress">
                                            <button type="submit" name="update_task" class="btn-done"
                                                style="background:#111827;">Start Task</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- In Progress -->
                    <div class="kanban-col">
                        <div class="col-header">
                            <div class="col-title"><span class="dot blue"></span> In Progress</div>
                            <div class="col-count"><?= count($tasks_in_progress) ?></div>
                        </div>
                        <div class="col-body">
                            <?php foreach ($tasks_in_progress as $t): ?>
                                <div class="task-card">
                                    <div class="task-dept">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z">
                                            </path>
                                        </svg>
                                        IT SECURITY
                                    </div>
                                    <div class="task-badges">
                                        <span class="badge-priority <?= strtolower($t['priority']) ?>"><?= $t['priority'] ?>
                                            Priority</span>
                                        <span class="badge-status">Active</span>
                                    </div>
                                    <div class="task-title"><?= htmlspecialchars($t['title']) ?></div>
                                    <div class="task-date">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="16" y1="2" x2="16" y2="6"></line>
                                            <line x1="8" y1="2" x2="8" y2="6"></line>
                                            <line x1="3" y1="10" x2="21" y2="10"></line>
                                        </svg>
                                        Due: <?= htmlspecialchars($t['deadline']) ?>
                                    </div>

                                    <div class="task-actions">
                                        <form method="POST" style="display:flex; width:100%; gap:10px;">
                                            <input type="hidden" name="task_id" value="<?= $t['id'] ?>">
                                            <input type="hidden" name="status" value="Completed">
                                            <button type="submit" name="update_task" class="btn-done">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff"
                                                    stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                                Done
                                            </button>
                                            <!-- EDIT BUTTON TRIGGERS JS MODAL -->
                                            <button type="button" class="btn-edit" 
                                                onclick="openEditModal(
                                                    <?= $t['id'] ?>, 
                                                    '<?= htmlspecialchars($t['title'], ENT_QUOTES, 'UTF-8') ?>', 
                                                    '<?= $t['priority'] ?>', 
                                                    '<?= $t['deadline'] ?>'
                                                )">Edit</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Completed -->
                    <div class="kanban-col">
                        <div class="col-header">
                            <div class="col-title"><span class="dot green"></span> Completed</div>
                            <div class="col-count"><?= count($tasks_completed) ?></div>
                        </div>
                        <div class="col-body">
                            <?php foreach ($tasks_completed as $t): ?>
                                <div class="task-card completed">
                                    <div class="task-dept">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z">
                                            </path>
                                        </svg>
                                        LEGAL
                                    </div>
                                    <div class="task-badges">
                                        <span class="badge-priority <?= strtolower($t['priority']) ?>"><?= $t['priority'] ?>
                                            Priority</span>
                                        <div class="badge-check">
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#fff"
                                                stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="task-title"><?= htmlspecialchars($t['title']) ?></div>
                                    <div class="task-date">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="16" y1="2" x2="16" y2="6"></line>
                                            <line x1="8" y1="2" x2="8" y2="6"></line>
                                            <line x1="3" y1="10" x2="21" y2="10"></line>
                                        </svg>
                                        Completed: <?= htmlspecialchars($t['deadline']) ?>
                                    </div>

                                    <div class="task-actions">
                                        <!-- Revert to In Progress button -->
                                        <form method="POST" style="width: 100%;">
                                            <input type="hidden" name="task_id" value="<?= $t['id'] ?>">
                                            <input type="hidden" name="status" value="In Progress">
                                            <button type="submit" name="update_task" class="btn-view">
                                                Revert to In Progress
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Add Task Model -->
    <div id="addTaskModal" class="modal-overlay">
        <div class="modal-content">
            <h2>Create New Task</h2>
            <form method="POST">

                <div class="input-group">
                    <label>Task Title</label>
                    <input type="text" name="title" required placeholder="E.g., Review compliance policy">
                </div>

                <div class="input-group">
                    <label>Priority</label>
                    <select name="priority" required>
                        <option value="Low">Low Priority</option>
                        <option value="Medium">Medium Priority</option>
                        <option value="High">High Priority</option>
                    </select>
                </div>

                <div class="input-group">
                    <label>Deadline</label>
                    <input type="date" name="deadline" required>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeTaskModal()">Cancel</button>
                    <button type="submit" name="add_task" class="btn-submit">Save Task</button>
                </div>

            </form>
        </div>
    </div>

    <!-- Edit Task -->
    <div id="editTaskModal" class="modal-overlay">
        <div class="modal-content">
            <h2>Edit Task</h2>
            <form method="POST">
                <input type="hidden" name="task_id" id="edit_task_id">

                <div class="input-group">
                    <label>Task Title</label>
                    <input type="text" name="title" id="edit_title" required>
                </div>

                <div class="input-group">
                    <label>Priority</label>
                    <select name="priority" id="edit_priority" required>
                        <option value="Low">Low Priority</option>
                        <option value="Medium">Medium Priority</option>
                        <option value="High">High Priority</option>
                    </select>
                </div>

                <div class="input-group">
                    <label>Deadline</label>
                    <input type="date" name="deadline" id="edit_deadline" required>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" name="edit_task" class="btn-submit">Save Changes</button>
                    <!-- DELETE BUTTON -->
                    <button type="submit" name="delete_task" class="btn-delete" onclick="return confirm('Are you sure you want to delete this task?');">Delete Task</button>
                </div>

            </form>
        </div>
    </div>

    <script>
        function openTaskModal() {
            document.getElementById('addTaskModal').style.display = 'flex';
        }

        function closeTaskModal() {
            document.getElementById('addTaskModal').style.display = 'none';
        }

        function openEditModal(id, title, priority, deadline) {
            document.getElementById('edit_task_id').value = id;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_priority').value = priority;
            document.getElementById('edit_deadline').value = deadline;
            document.getElementById('editTaskModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editTaskModal').style.display = 'none';
        }

        window.onclick = function (event) {
            var addModal = document.getElementById('addTaskModal');
            var editModal = document.getElementById('editTaskModal');
            if (event.target == addModal) {
                closeTaskModal();
            }
            if (event.target == editModal) {
                closeEditModal();
            }
        }
    </script>

</body>

</html>
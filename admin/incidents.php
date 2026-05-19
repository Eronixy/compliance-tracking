<?php
session_start();
include(__DIR__ . '/../config/connect.php');

if ($_SESSION['role'] != 'admin') {
    exit("Access Denied");
}

/* HANDLE UPDATE STATUS */
if (isset($_POST['update_incident'])) {

    $id = $_POST['incident_id'];
    $status = $_POST['status'];
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);

    mysqli_query($conn, "
        UPDATE incident_reports
        SET status = '$status',
            description = CONCAT(description, '\n\n[ADMIN NOTE] ', '$notes')
        WHERE id = $id
    ");
}

/* HANDLE ESCALATION */
if (isset($_POST['escalate'])) {

    $id = $_POST['incident_id'];
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);

    mysqli_query($conn, "
        UPDATE incident_reports
        SET status = 'Escalated',
            description = CONCAT(description, '\n\n[ESCALATION REASON] ', '$reason')
        WHERE id = $id
    ");

    /* OPTIONAL: log escalation into audit_logs if you have it */
    mysqli_query($conn, "
        INSERT INTO activity_logs (user_id, action, task_id)
        SELECT user_id, CONCAT('ESCALATED INCIDENT ID: ', id), id
        FROM incident_reports
        WHERE id = $id
    ");
}

/* FETCH DATA */
$data = mysqli_query($conn, "
SELECT incident_reports.*, users.username, users.department
FROM incident_reports
INNER JOIN users ON users.id = incident_reports.user_id
ORDER BY date_reported DESC
");
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
                <h1>Incident Response Center</h1>
            </div>

            <!-- CONTENT BODY -->
            <div class="content-body">

            <!-- PAGE HEADER -->
            <div class="incident-hero">
                <h2 class="incident-title">Incident Response Center</h2>
                <p class="incident-subtitle">Review, resolve, and escalate employee incident reports. Prioritize actions based on severity indicators and maintain a clear audit trail.</p>
            </div>

            <!-- INCIDENTS TABLE CARD -->
            <div class="incidents-card">
                <h3 class="incidents-section-title">Department Performance</h3>
                
                <div class="incidents-table-wrapper">
                    <table class="incidents-table">
                        <thead>
                            <tr>
                                <th>USER</th>
                                <th>TITLE</th>
                                <th>DESCRIPTION</th>
                                <th>SEVERITY</th>
                                <th>STATUS</th>
                                <th>PROOF</th>
                                <th>ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($i = mysqli_fetch_assoc($data)) { 
                                $severityClass = strtolower($i['severity']);
                                $statusClass = strtolower(str_replace(' ', '-', $i['status']));
                                $userInitials = strtoupper(substr($i['username'], 0, 2));
                            ?>
                            <tr>
                                <td>
                                    <div class="incident-user-cell">
                                        <div class="incident-avatar"><?= $userInitials ?></div>
                                        <div class="incident-user-info">
                                            <div class="incident-username"><?= htmlspecialchars($i['username']) ?></div>
                                            <div class="incident-department"><?= htmlspecialchars($i['department'] ?? 'N/A') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($i['title']) ?></strong>
                                </td>
                                <td>
                                    <span class="incident-desc"><?= nl2br(htmlspecialchars(substr($i['description'], 0, 50))) ?></span>
                                </td>
                                <td>
                                    <span class="severity-badge severity-<?= $severityClass ?>">
                                        ● <?= ucfirst($i['severity']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $statusClass ?>">
                                        <?= ucfirst($i['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($i['proof_image'])) { ?>
                                        <a href="../shared/uploads/<?= $i['proof_image'] ?>" target="_blank" class="proof-link">View Log</a>
                                    <?php } else {
                                        echo "—";
                                    } ?>
                                </td>
                                <td>
                                    <div class="incident-actions">
                                        <form method="POST" class="incident-action-form">
                                            <input type="hidden" name="incident_id" value="<?= $i['id'] ?>">
                                            <select name="status" class="action-select">
                                                <option value="">Set Status...</option>
                                                <option value="Under Review">Under Review</option>
                                                <option value="Resolved">Resolved</option>
                                            </select>
                                        </form>
                                        
                                        <form method="POST" class="incident-escalate-form">
                                            <input type="hidden" name="incident_id" value="<?= $i['id'] ?>">
                                            <input type="text" name="reason" placeholder="Escalation reason..." class="escalation-input" required>
                                            <button type="submit" name="escalate" class="btn-escalate">Escalate ►</button>
                                        </form>
                                    </div>
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
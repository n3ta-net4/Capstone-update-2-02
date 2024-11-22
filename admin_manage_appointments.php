<?php
if(!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    try {
        $pdo->beginTransaction();
        
        $appointmentId = $_POST['appointment_id'];
        
        $stmtNotif = $pdo->prepare('DELETE FROM notifications WHERE appointment_id = ?');
        $stmtNotif->execute([$appointmentId]);
        
        $stmtAppt = $pdo->prepare('DELETE FROM appointments WHERE id = ?');
        $stmtAppt->execute([$appointmentId]);
        
        $pdo->commit();
        $_SESSION['success'] = "Appointment deleted successfully.";
        header("Location: admin_manage_appointments.php");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error deleting appointment. Please try again.";
        header("Location: admin_manage_appointments.php");
        exit();
    }
}

$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$date = isset($_GET['date']) ? $_GET['date'] : '';

$query = "SELECT a.*, u.name as user_name, u.email, a.notes 
          FROM appointments a 
          JOIN users u ON a.user_id = u.id 
          WHERE a.status != 'pending'";
$params = [];

if ($status !== 'all') {
    $query .= " AND a.status = ?";
    $params[] = $status;
}

if ($date) {
    $query .= " AND a.appointment_date = ?";
    $params[] = $date;
}

$query .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$appointments = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage All Appointments</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin_manage_appointments.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <a href="admin_dashboard.php">
                <img src="css/aw-k9.png" alt="aw-k9 logo">
            </a>
        </div>
        <h2>Admin Dashboard</h2>
        <hr>
        <ul>
            <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="admin_appointments.php"><i class="fas fa-calendar-check"></i> Pending Appointments</a></li>
            <li><a href="admin_bookings.php"><i class="fas fa-paw"></i> Pending Reservations</a></li>
            <li><a href="admin_manage_appointments.php" class="active"><i class="fas fa-calendar"></i> Manage Appointments</a></li>
            <li><a href="admin_manage_reservations.php"><i class="fas fa-hotel"></i> Manage Reservations</a></li>
            <li><a href="admin_services.php"><i class="fas fa-bone"></i> Manage Services</a></li>
            <li><a href="admin_boarding_rates.php"><i class="fas fa-dollar-sign" style="width: 16px; text-align: center;"></i> Manage Rates</a></li>
            <li><a href="admin_feedback.php"><i class="fas fa-comments"></i> Manage Feedback</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1>Manage All Appointments</h1>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <div class="filters">
            <form method="GET" class="filter-group">
                <label for="status">Status:</label>
                <select name="status" id="status">
                    <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All</option>
                    <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>

                <label for="date">Date:</label>
                <input type="date" name="date" id="date" value="<?= htmlspecialchars($date) ?>">

                <button type="submit" class="filter-btn">Apply Filters</button>
            </form>
        </div>

        <?php foreach($appointments as $appointment): ?>
            <div class="appointment-card">
                <div class="appointment-info">
                    <h3>Appointment Details</h3>
                    <p><span class="info-label">Client:</span><?= htmlspecialchars($appointment['user_name']) ?></p>
                    <p><span class="info-label">Email:</span><?= htmlspecialchars($appointment['email']) ?></p>
                    <p><span class="info-label">Date:</span><?= htmlspecialchars($appointment['appointment_date']) ?></p>
                    <p><span class="info-label">Time:</span><?= htmlspecialchars($appointment['appointment_time']) ?></p>
                    <p>
                        <span class="info-label">Status:</span>
                        <span class="status-badge status-<?= $appointment['status'] ?>">
                            <?= ucfirst(htmlspecialchars($appointment['status'])) ?>
                        </span>
                    </p>
                    <?php if (!empty($appointment['notes'])): ?>
                        <p><span class="info-label">Notes:</span><?= htmlspecialchars($appointment['notes']) ?></p>
                    <?php endif; ?>
                    <?php if ($appointment['status'] === 'rejected' && !empty($appointment['rejection_reason'])): ?>
                        <div class="rejection-reason">
                            <span class="info-label">Rejection Reason:</span><?= htmlspecialchars($appointment['rejection_reason']) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="button-group">
                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this appointment?');">
                        <input type="hidden" name="appointment_id" value="<?= $appointment['id'] ?>">
                        <button type="submit" name="delete" class="delete-btn">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if(empty($appointments)): ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <p>No appointments found matching the selected criteria.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

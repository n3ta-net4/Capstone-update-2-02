<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $booking_id = $_POST['booking_id'];
    $stmt = $pdo->prepare("DELETE FROM pet_boarding WHERE id = ?");
    $stmt->execute([$booking_id]);
    $_SESSION['success'] = "Reservation deleted successfully.";
    header("Location: admin_manage_reservations.php");
    exit();
}

$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$query = "
    SELECT pb.id, pb.pet_name, pb.pet_type, pb.status, pb.notes,
           DATE(pb.check_in) as check_in, 
           DATE(pb.check_out) as check_out, 
           pb.rejection_reason,
           a.type, a.number, u.name as client_name 
    FROM pet_boarding pb 
    JOIN accommodations a ON pb.accommodation_id = a.id 
    JOIN users u ON pb.user_id = u.id 
    WHERE pb.status != 'pending'";

$params = [];

if ($status !== 'all') {
    $query .= " AND pb.status = ?";
    $params[] = $status;
}

if ($start_date) {
    $query .= " AND DATE(pb.check_in) >= ?";
    $params[] = $start_date;
}

if ($end_date) {
    $query .= " AND DATE(pb.check_in) <= ?";
    $params[] = $end_date;
}

$query .= " ORDER BY pb.check_in DESC";

$stmt = empty($params) ? 
    $pdo->query($query) : 
    $pdo->prepare($query);

if (!empty($params)) {
    $stmt->execute($params);
}

$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage All Reservations</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin_manage_reservations.css">
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
            <li><a href="admin_manage_appointments.php"><i class="fas fa-calendar"></i> Manage Appointments</a></li>
            <li><a href="admin_manage_reservations.php" class="active"><i class="fas fa-hotel"></i> Manage Reservations</a></li>
            <li><a href="admin_services.php"><i class="fas fa-bone"></i> Manage Services</a></li>
            <li><a href="admin_boarding_rates.php"><i class="fas fa-dollar-sign" style="width: 16px; text-align: center;"></i> Manage Rates</a></li>
            <li><a href="admin_feedback.php"><i class="fas fa-comments"></i> Manage Feedback</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1>Manage All Reservations</h1>
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

                <label for="start_date">Start Date:</label>
                <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($start_date) ?>">

                <label for="end_date">End Date:</label>
                <input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($end_date) ?>">

                <button type="submit" class="filter-btn">Apply Filters</button>
            </form>
        </div>

        <?php foreach ($bookings as $booking): ?>
            <div class="reservation-card">
                <div class="reservation-info">
                    <h3>Reservation Details</h3>
                    <p><span class="info-label">Client:</span> <?= htmlspecialchars($booking['client_name']) ?></p>
                    <p><span class="info-label">Pet Name:</span> <?= htmlspecialchars($booking['pet_name']) ?></p>
                    <p><span class="info-label">Pet Type:</span> <?= htmlspecialchars($booking['pet_type']) ?></p>
                    <p><span class="info-label">Accommodation:</span> <?= htmlspecialchars($booking['type']) ?> <?= htmlspecialchars($booking['number']) ?></p>
                    <p><span class="info-label">Check In:</span> <?= htmlspecialchars($booking['check_in']) ?></p>
                    <p><span class="info-label">Check Out:</span> <?= htmlspecialchars($booking['check_out']) ?></p>
                    <p><span class="info-label">Status:</span> 
                        <span class="status-badge status-<?= $booking['status'] ?>">
                            <?= ucfirst(htmlspecialchars($booking['status'])) ?>
                        </span>
                    </p>
                    <?php if (!empty($booking['notes'])): ?>
                        <p><span class="info-label">Notes:</span> <?= htmlspecialchars($booking['notes']) ?></p>
                    <?php endif; ?>
                    <?php if ($booking['status'] === 'rejected' && !empty($booking['rejection_reason'])): ?>
                        <div class="rejection-reason">
                            <span class="info-label">Rejection Reason:</span> <?= htmlspecialchars($booking['rejection_reason']) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this reservation?');">
                    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                    <button type="submit" name="delete" class="delete-btn">
                        <i class="fas fa-trash"></i> Delete Reservation
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
        
        <?php if(empty($bookings)): ?>
            <div class="empty-state">
                <i class="fas fa-hotel"></i>
                <p>No reservations found matching the selected criteria.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function confirmDelete() {
            return confirm('Are you sure you want to delete this reservation?');
        }
    </script>
</body>
</html>

